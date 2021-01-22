import { config } from '../config'

export function canFederateWith(domain: string): boolean {
  if (!config('federation.enabled')) {
    return false;
  }
  if (config('federation.acceptFrom').length > 0) {
    return config('federation.acceptFrom').contains(domain);
  }
  if (config('federation.rejectFrom').length > 0) {
    return !config('federation.rejectFrom').contains(domain);
  }
  return true;
}
