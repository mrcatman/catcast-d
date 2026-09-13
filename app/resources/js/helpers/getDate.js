export default function (date, showMS = true) {
	if (!(typeof date === 'Date')) {
		date = new Date(date);
	}
	let dd = date.getDate();
	let mm = date.getMonth()+1; //January is 0!
	let yyyy = date.getFullYear();
	let hh = date.getHours();
	let ii = date.getMinutes();
	if(dd < 10) {
		dd = '0' + dd
	}

	if(mm < 10) {
		mm = '0' + mm
	}
	if(hh < 10) {
		hh = '0' + hh
	}

	if(ii < 10) {
		ii = '0' + ii
	}
	let dateText = dd + '.' + mm + '.' + yyyy;
	if (showMS)  {
	  dateText+= ' ' + hh + ':' + ii;
  }
	return dateText;
	//return date;
};
