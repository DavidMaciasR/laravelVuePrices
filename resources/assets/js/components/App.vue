<template>
    <div id="app">
        <div class="heading">
            <h1>Calculate your product price </h1>
        </div>
        <fieldset>
            <h3>Insert the product skus separated by commas</h3>
            <input @keyup="calculatePrices" v-model="skuInput" type="text"/>
            <h4>(Optional) Insert the account reference</h4>
            <input @keyup="calculatePrices" v-model="accountInput" type="text"/>
        </fieldset>

        <fieldset><h5>Product price live feed</h5><span>{{this.currentPriceFeedValue}}</span>


        </fieldset>

        <fieldset id="purchaseTotals">
            <h2>Purchase details</h2>
            <h5>Live feed price taken for this purchase: {{this.lastSelectedLiveFeedValue}}</h5>
            <h3>Final price for each product: </h3>
            <p>{{this.productPrices}}</p>
            <h4>The total price for the selected products is: </h4>
            <p>{{this.priceQuote}}</p>

        </fieldset>
        <fieldset id="infoMsg">{{this.infoMsg}} </fieldset>
    </div>
</template>

<script>
    export default {

        data() {
            return {
                working: false,
                skuInput: '',
                accountInput: '',
                priceQuote: 0.0,
                currentPriceFeedValue: '',
                lastSelectedLiveFeedValue: '',
                productPrices: [],
                infoMsg: ''
            }
        },
        methods: {
            calculatePrices() {
                this.mute = true;
                this.lastSelectedLiveFeedValue= this.currentPriceFeedValue;
                let params = {'accountInput': this.accountInput, 'skuInput': this.skuInput, 'liveFeedPrice': this.currentPriceFeedValue};
                window.axios.put(`/api/cruds/`, {params}).then((data) => {


                    if(data['data']['totalValue']) {
                        $('#purchaseTotals').show();
                        $('#infoMsg').hide();

                        this.priceQuote = data['data']['totalValue'];
                        this.productPrices= data['data']['productPrices'];

                    }else{
                        this.infoMsg= 'Unfortunately this selection of products did\'t have prices available. ';

                        $('#purchaseTotals').hide();
                        $('#infoMsg').show();
                    }
                    this.mute = false;
                });
            },

            liveFeed() {
                this.mute = true;
                window.axios.get(`/api/mockAPI/priceFeed`).then((data) => {

                    this.currentPriceFeedValue = data['data'];
                    this.mute = false;
                })
            },
        },
        watch: {
            mute(val) {
                document.getElementById('mute').className = val ? "on" : "";
            }
        },
        created() {
            this.interval = setInterval(() => this.liveFeed(), 1200);
        }
    }
</script>
<style>
    #app {
        margin-left: 1em;
    }

    .heading h1 {
        margin-bottom: 0;
    }

    #purchaseTotals, #infoMsg {
        display: none;
    }

    fieldset {
        margin: 40px 20px;
    }

    *{ font-family: monospace}
</style>
