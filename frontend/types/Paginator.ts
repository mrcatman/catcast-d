export default interface Paginator<T> {
  total: Number;
  list: Array<T>;
  page: Number;
  pagesCount: Number;
}
