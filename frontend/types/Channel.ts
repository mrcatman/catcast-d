import Picture from '~/types/Picture';
import Stream from '~/types/Stream'
export default interface Channel {
  id?: number;
  url: string;
  name: string;
  description: string;
  picture?: Picture;
  current_stream?: Stream;
  is_online?: boolean;

  domain?: string;
  live_url?: string;
  web_url?: string;
}
