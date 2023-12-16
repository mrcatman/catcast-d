import { distanceInWords, format } from 'date-fns';

const locales = {
  en: require('date-fns/locale/en'),
  ru: require('date-fns/locale/ru')
};

export function getLocales() {
  return locales;
}
export function formatPublishDate(date, isTimestamp = false) {
  const now = new Date();
  const then = new Date(isTimestamp ? date * 1000 : date);

  const secondsInMonth = 60 * 60 * 24 * 30 * 1000;
  if (now.getTime() - then.getTime() < secondsInMonth) {
    return distanceInWords(
      now,
      then,
      {
        addSuffix: true,
        locale: locales[window.__locale__]
      }
    );
  } else {
    const startOfCurrentYear = new Date(new Date().getFullYear(), 0, 1);
    const dateFormat = then.getTime() < startOfCurrentYear.getTime() ? 'D MMMM YYYY' : 'D MMMM';
    return format(then,
      dateFormat,
      {locale: locales[window.__locale__]}
    );
  }
}

export function formatFullDate(date, {isTimestamp = false, seconds = false} = {}) {
  let dateObject = new Date(isTimestamp ? date * 1000 : date);
  let dateFormat = "DD.MM.YYYY H:mm" + (seconds ? ":ss" : "");
 // if (window.__locale__ === "ru") {
 //   dateFormat = "DD.MM.YYYY в H:mm" + (seconds ? ":ss" : "");
 // }
  return format(dateObject,
    dateFormat,
    {locale: locales[window.__locale__]}
  );
}

export function getDate(ts) {
  let then = new Date(ts);
  let dateFormat = "DD MMMM";
  let result = format(then,
    dateFormat,
    {locale: locales[window.__locale__]}
  );
  return result;
}


export function getDateWithYear(ts) {
  let then = new Date(ts * 1000);
  let dateFormat = "DD.MM.YYYY";
  let result = format(then,
    dateFormat,
    {locale: locales[window.__locale__]}
  );
  return result;
}


export function getTime(ts) {
  let then = new Date(ts * 1000);
  let dateFormat = "H:mm:ss";
  let result = format(then,
    dateFormat,
  );
  return result;
}


export function formatDuration(time) {
  time = Math.ceil(time);
  let hoursString, minutesString, secondsString,hours, minutes = 0;
  hours = Math.floor( time / 3600);
  time = time % 3600;
  minutes = Math.floor( time / 60);
  time = time % 60;
  hoursString = hours.toString();
  minutesString = (minutes >= 10) ? minutes.toString() : '0' + minutes.toString();
  secondsString = (time >= 10) ? time.toString() : '0' + time.toString();
  return (((hours > 0) ? hoursString + ':' : '') + minutesString + ':' + secondsString);
}

