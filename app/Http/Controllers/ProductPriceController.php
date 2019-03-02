<?php

namespace App\Http\Controllers;

use App\Product;
use App\Price;
use App\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductPriceController extends Controller
{

    public function priceQuote(Request $request)
    {
        $params = $request->params;
        $skus = explode(',', $params['skuInput']);
        $account = $params['accountInput'];
        $feedPrice = $params['liveFeedPrice'];


        return response($this->calculateTotalPrice(Product::whereIn('sku', $skus)->get(), $account, $feedPrice, $skus), Response::HTTP_OK);
    }

    public function calculateTotalPrice($productList, $account, $feedPrice, $skus)
    {
        $productPrices = [];
        $totalValue = 0.0;

        foreach ($productList as $product) {
            list($productPrices, $totalValue) = $this->processProduct($account, $feedPrice, $product, $productPrices, $totalValue, $skus);
        }
        return ['totalValue' => $totalValue, 'productPrices' => $productPrices];
    }

    /**
     * @param $account
     * @param $product
     * @return float
     */
    public function calculateProductValue($account, $product): float
    {
        $productValue = 0.0;
        foreach (Price::where('product_id', '=', $product->id)->get() as $price) {
            if (!$price->account_id) {
                if ($productValue == 0.0 || $price->value < $productValue) {
                    $productValue = $price->value;
                }
            } else if ($account && (Account::where('external_reference', '=', $account)->firstOrFail()) && Account::where('external_reference', '=', $account)->firstOrFail()->id == $price->account_id) {
                return $price->value;
            }
        }
        return $productValue;
    }

    /**
     * get a random price from the json prices file simulating a live stream
     * @return string
     */
    public function mockPriceFeed()
    {
        $jsonString = file_get_contents(base_path('resources/assets/productFeed/live_prices.json'));
        $data = json_decode($jsonString, true);

        return json_encode($data[random_int(0, count($data)-1)]);

    }

    /**
     * @param $account
     * @param $feedPrice
     * @param $productValue
     * @return mixed
     */
    public function adjustToPriceFeed($account, $feedPrice, $productValue)
    {
        if ((!array_key_exists('account', $feedPrice)) || ($feedPrice['account'] == $account)) {
            $productValue = $feedPrice['price'];

        }
        return $productValue;
    }

    /**
     * @param $account
     * @param $feedPrice
     * @param $product
     * @param $productPrices
     * @param $totalValue
     * @param $skus
     * @return array
     */
    public function processProduct($account, $feedPrice, $product, $productPrices, $totalValue, $skus): array
    {
        $productValue = $this->calculateProductValue($account, $product);
        if ($product->sku == $feedPrice['sku']) {
            $productValue = $this->adjustToPriceFeed($account, $feedPrice, $productValue);
        }
        if ($productValue) {
            $productPrices[] = $product->sku . ' : ' . $productValue;
        }
        $totalValue += ($productValue * array_count_values($skus)[$product->sku]);
        return array($productPrices, $totalValue);
    }

}
