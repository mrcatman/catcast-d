
const defaultPaginatorState = {
	current_page: 1,
	data: [],
	from: 1,
	last_page: 1,
	links: [],
	per_page: 30,
	to: 1,
	total: 0
}

export type PaginatedRequestHandler<T> = (args: Api.PaginatedQuery) => Promise<Api.PaginatedResponse<T>>;

export const usePaginatedData = <T>(requestHandler: PaginatedRequestHandler<T>) => {

	const currentPage = ref<number>(1);
	const loading = ref<boolean>(false);
	const loadedInitial = ref<boolean>(false);

	const showPager = ref<boolean>(false);

	const items = ref<T[]>([]);
	const total = ref<number>(0);
	const pagesCount = ref<number>(1);
	const empty = ref<boolean>(false);

	const load = async () => {
		setPage(1);
	}

	const resetItems = () => {
		items.value = [];
	}

	const setPage = async(page: number) => {
		resetItems();
		currentPage.value = page;

		items.value = await getItems();
	}

	const getItems = async () => {
		loading.value = true;

		const response = await requestHandler({
			page: currentPage.value
		})

		showPager.value = response.last_page > 1;
		total.value = response.total;
		pagesCount.value = response.last_page;
		empty.value = response.total === 0;

		loading.value = false;
		loadedInitial.value = true;

		return response.data;

		//if (!this.loading && this.currentPage < this.comments.last_page) {
		//     this.loading = true;
		//     this.currentPage++;
		//     const data = await this.$api.get(`${this.baseUrl}`, {onError: this.$api.defaultPaginator});
		//     this.comments.data = [...this.comments.data, ...data.data];
		//     this.comments.total = data.total;
		//     this.loading = false;
		//   }
	}

	const loadMore = async () => {
		if (currentPage.value >= pagesCount.value) {
			return;
		}
		currentPage.value = currentPage.value + 1;

		const newItems = await getItems();
		items.value = [...items.value, ...newItems];
	}

	const loadPrevious = async() => {
		console.log('load prev'); // todo
	}

	const addItem = (item: T) => {
		items.value = [item, ...items.value];
	}

	const updateItem = (item: T) => {
		console.log(item); // todo
	}

	return {
		items,
		empty,
		total,
		load,
		loadMore,
		loadPrevious,


		currentPage,
		setPage,
		loading,
		loadedInitial,

		showPager,
		pagesCount,

		addItem,
		updateItem
	}
}
