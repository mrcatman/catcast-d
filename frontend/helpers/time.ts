export function getReadableTime(ts: any): string {
  return new Date(ts).toLocaleTimeString();
}
