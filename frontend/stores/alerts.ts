import { defineStore } from "pinia";

export interface AlertItem {
	type: 'success' | 'error';
	text: string;
}

type AlertItemWithId = AlertItem & {
	id: number;
}

const ALERT_VISIBILITY_TIME = 5000;

export const useAlertsStore = defineStore('alerts', () => {

	const alerts = ref<AlertItemWithId[]>([]);

	const newAlert = (alert: AlertItem) => {
		const randomId = Math.floor(Math.random() * 1000000);
		alerts.value.push({
			...alert,
			id: randomId
		});
		setTimeout(()=>{
			alerts.value = alerts.value.filter(alert=>{
				return alert.id !== randomId;
			});
		}, ALERT_VISIBILITY_TIME);
	}

	return {
		alerts,
		newAlert
	}
});
