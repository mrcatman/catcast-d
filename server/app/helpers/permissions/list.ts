export enum UserChannelPermissions {
  MODERATE_CHAT = 'MODERATE_CHAT',
  EDIT_STREAM_INFO = 'EDIT_STREAM_INFO',
  BROADCAST = 'BROADCAST',
  EDIT_CHANNEL_INFO = 'EDIT_CHANNEL_INFO',
  PUBLISH_NEWS = 'PUBLISH_NEWS',
}
export const RemoteAvailableChannelPermissions = [
  UserChannelPermissions.MODERATE_CHAT
]
// not all permissions will be available for remote users :( (at least for now)
// for example it'll be possible to moderate channel's chat
// but not possible to edit channel settings
