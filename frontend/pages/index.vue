<template>
  <div>
    <v-toolbar dark color="#1d2632">
      <v-toolbar-title>Ativo:</v-toolbar-title>
      <v-autocomplete
        v-model="selectedAsset"
        :loading="loading"
        :items="assets.map((item) => item.Asst)"
        :search-input.sync="search"
        cache-items
        class="mx-4"
        flat
        hide-no-data
        hide-details
        label="Selecione o ativo"
        solo-inverted
        @change="getData"
      ></v-autocomplete>
    </v-toolbar>
    <v-container fluid>
      <h1 class="text-center" v-if="!showChart">
        Selecione o ativo no select acima
      </h1>

      <div class="text-center">
        <v-progress-circular
          :size="170"
          :width="7"
          color="#1d2632"
          indeterminate
          v-if="loading"
        ></v-progress-circular>
      </div>
      <v-row>
        <v-col cols="12">
          <LineChart :data="chartData" :options="options" v-if="showChart" />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import moment from "moment";

export default {
  head() {
    return {
      title: "Home - Jumba Test",
    };
  },
  data: () => ({
    moment: moment,
    loading: false,
    showChart: false,
    assets: [],
    search: null,
    selectedAsset: "",
    dataSelectedAsset: [],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      title: {
        display: true,
        text: `Quantidade de Saldo do Ativo e Preço Médio`,
      },
      legend: {
        display: true,
      },
      scales: {
        yAxes: [
          {
            id: "quantity",
            type: "linear",
            position: "right",
            beginAtZero: true,
          },
          {
            id: "averagePrice",
            type: "linear",
            position: "left",
            beginAtZero: true,
          },
          {
            id: "totalBalance",
            type: "linear",
            position: "right",
            beginAtZero: true,
          },
        ],
      },
      elements: {
        line: {
          tension: 0, // disables bezier curves
        },
      },
      tooltips: {
        mode: "index",
        intersect: false,
        callbacks: {
          label: function (tooltipItem, data) {
            var label =
              data.datasets[tooltipItem.datasetIndex].label + ": " || "";

            if (label !== "Quantidade de Saldo: ") {
              label += Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL",
              }).format(tooltipItem.value);
            } else {
              label += Intl.NumberFormat("pt-BR").format(tooltipItem.value);
            }
            return label;
          },
        },
      },
    },
  }),

  async mounted() {
    await this.$axios
      .get("http://localhost:4000/api/list-all-unique-assets")
      .then((response) => {
        this.assets = response.data;
      })
      .catch((error) => {
        console.log(error);
      });
  },

  methods: {
    async getData(asset) {
      this.loading = true;
      await this.$axios
        .get("http://localhost:4000/api/list-position-by-asset", {
          params: {
            asset: asset,
          },
        })
        .then((response) => {
          this.dataSelectedAsset = response.data;
          this.showChart = true;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },

  computed: {
    chartData() {
      return {
        labels: this.dataSelectedAsset.map((item) =>
          moment(item.RptDt).format("DD/MM/YYYY")
        ),
        datasets: [
          {
            label: "Quantidade de Saldo",
            backgroundColor: "#16d4e9",
            borderColor: "#16d4e9",
            data: this.dataSelectedAsset.map((item) => item.BalQty),
            yAxisID: "quantity",
            order: 3,
            type: "bar",
          },
          {
            label: "Preço Médio",
            backgroundColor: "#00FF00",
            borderColor: "#00FF00",
            data: this.dataSelectedAsset.map((item) => {
              let price = item.TradAvrgPric.replace(",", ".");
              return parseFloat(price).toFixed(2);
            }),
            type: "line",
            yAxisID: "averagePrice",
            order: 2,
            fill: false,
          },
          {
            label: "Saldo Total",
            backgroundColor: "#0000FF",
            borderColor: "#0000FF",
            data: this.dataSelectedAsset.map((item) => {
              let price = item.BalVal.replace(",", ".");
              return parseFloat(price).toFixed(2);
            }),
            type: "line",
            yAxisID: "totalBalance",
            order: 1,
            fill: false,
          },
        ],
      };
    },
  },
};
</script>

<style>
</style>