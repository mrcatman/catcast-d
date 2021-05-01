
export default interface Stream {
  id?: number;
  name: string;
  watch_url: string;
  cover_url: string;
  started_at: Date;
  ended_at?: Date;
}
