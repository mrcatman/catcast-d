import { Line } from 'vue-chartjs'

function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}



export default {
  extends: Line,
  methods: {
    render() {
      this.renderChart({
        labels: this.labels,
        datasets: this.data.map((item, index) => {
          return {
            label: this.titles ? this.titles[index] : "",
            borderColor: this.colors ? this.colors[index] : getComputedStyle(document.getElementById('app')).getPropertyValue('--active-color'),
            data: item
          }
        })
      }, {
        maintainAspectRatio: false,
        legend: {
          display: this.displayLegend,
          position: 'bottom',
          labels: {

          }
        },
        scales: {
          yAxes: [{
            ticks: {
              stepSize: 1
            }
          }],
        },
        tooltips: {
          callbacks: {
            label: (tooltipItem, data) =>  {
              if (this.tooltips) {
                return `${this.tooltips[tooltipItem.datasetIndex]}: ${tooltipItem.yLabel}`;
              }
              return tooltipItem.yLabel;
            }
          }
        }
      })
    }
  },
  watch: {
    data() {
      this.render();
    }
  },
  mounted () {
    this.render();
  },
  props: {
    displayLegend: {
      type: Boolean,
      required: false,
      default: true
    },
    titles: Array,
    colors: Array,
    tooltips: Array,
    labels: Array,
    data: {
      type: Array,
      required: true,
    }
  }
}
