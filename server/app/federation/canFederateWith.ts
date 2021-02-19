import { config } from '../config'

export function canFederateWith(domain: string): boolean {
  if (!config('federation.enabled')) {
    return false;
  }
  if (config('federation.accept_from').length > 0) {
    return config('federation.accept_from').contains(domain);
  }
  if (config('federation.reject_from').length > 0) {
    return !config('federation.reject_from').contains(domain);
  }
  return true;
}
