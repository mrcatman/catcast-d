import Picture from '~/types/Picture'

export default interface Channel {
  id?: number;
  url: string;
  name: string;
  description: string;
  picture: Picture | null;
  is_online?: boolean;
  live_url?: string;
}
