import { useI18n } from "vue-i18n";
import { format, formatDistance } from "date-fns";
import { enUS, ru } from "date-fns/locale";
const locales = {
	en: enUS,
	ru
};

const SECONDS_IN_MONTH = 60 * 60 * 24 * 30 * 1000;

const DateFormatPresets = {
	'full': 'dd.MM.yyyy H:mm',
	'fullWithSeconds': 'dd.MM.yyyy H:mm:ss',
}

export const useDates = () => {
	const { locale } = useI18n();

	const formatTimeAgo = (date: string | number, isTimestamp?: boolean) => {
		const now = new Date();
		const then = new Date(isTimestamp ? (date as number) * 1000 : date);


		if (now.getTime() - then.getTime() < SECONDS_IN_MONTH) {
			return formatDistance(
				now,
				then,
				{
					addSuffix: true,
					locale: locales[locale.value] ?? 'en'
				}
			);
		} else {
			const startOfCurrentYear = new Date(new Date().getFullYear(), 0, 1);
			const dateFormat = then.getTime() < startOfCurrentYear.getTime() ? 'd MMMM yyyy' : 'd MMMM';
			return format(then,
				dateFormat,
				{
					locale: locales[locale.value] ?? 'en'
				}
			);
		}
	}

	const formatDate = (date: string | number, preset: keyof typeof DateFormatPresets = 'full', isTimestamp?: boolean) => {
		let dateObject = new Date(isTimestamp ? (date as number) * 1000 : date);
		let dateFormat = DateFormatPresets[preset];

		return format(dateObject,
			dateFormat,
			{
				locale: locales[locale.value] ?? 'en'
			}
		);
	}

	return {
		formatTimeAgo,
		formatDate
	}
}
