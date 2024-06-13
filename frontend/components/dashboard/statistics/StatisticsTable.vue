<template>
  <div>
    <table class="table" v-for="table in chartData">
      <thead>
      <tr>
        <td :key="$index" v-for="(col, $index) in table.headings">{{ $t(col) }}</td>
      </tr>
      </thead>
      <tbody>
      <tr :key="$index" v-for="(row, $index) in table.values">
        <td :key="$index2" v-for="(col, $index2) in row">{{ col }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>
<script>
export default {
  props: {
    chartData: {
      type: Array,
      required: true
    }
  }
}
</script>
