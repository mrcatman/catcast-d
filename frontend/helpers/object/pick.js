const pick = (obj, params) => {
  const newObj = {};
  params.forEach(param => {
    if (obj[param]) {
      newObj[param] = obj[param];
    }
  })
  return newObj;
}
export default pick;
