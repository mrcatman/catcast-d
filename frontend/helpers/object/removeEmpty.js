const removeEmpty = (obj) => Object.fromEntries(Object.entries(obj).filter(([_, v]) => v != null && v.length));

export default removeEmpty;
