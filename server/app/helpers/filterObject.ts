export default  function filterObject(obj: any, props: Array<String>) {
  for (let key in obj) {
    if (props.indexOf( key ) == -1) {
      delete obj[key];
    }
  }
}
