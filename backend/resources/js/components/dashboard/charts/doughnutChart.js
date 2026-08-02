import { Doughnut } from 'vue-chartjs'

export default {
  extends: Doughnut,
  mounted () {
    this.renderChart({
      labels: this.labels,
      datasets: [
        {
          //label: this.title,
          backgroundColor: [
            'rgb(255, 159, 64)',
            'rgb(255, 205, 86)',
            'rgb(0, 163, 51)',
            'rgb(54, 162, 235)',
            'rgb(153, 102, 255)',
            'rgb(201, 203, 207)',
            'rgb(0,0,255)'
          ],
          data: this.data
        }
      ]
    }, {
      segmentShowStroke: false,
      elements: {
        arc: {
          borderWidth: 0
        }
      },
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        display: false
      },
    })
  },
  props: {
    labels: {
      type: Array,
      required: true,
    },
    title: {
      type: String,
      required: false,
    },
    data: {
      type: Array,
      required: true,
    }
  }
}
