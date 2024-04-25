export default function (str,maxLength) {
  let ending = '...';
  if (!str) {
    return "";
  }
	if (str.length <= maxLength) {
		return str;
	} else {
		return str.substr(0,maxLength)+'...';
	}
}
