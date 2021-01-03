export const context = [
  'https://www.w3.org/ns/activitystreams',
  'https://w3id.org/security/v1',
  {
    sc: 'http://schema.org#',
    catcast_object_type: {
      '@type': 'sc:Text', '@id': 'catcast:catcast_object_type'
    },
    broadcaster: {
      '@type': '@id', '@id': 'catcast:broadcaster'
    },
    channel: {
      '@type': '@id', '@id': 'catcast:channel'
    },
    ended_at: {
      '@type': 'sc:Time', '@id': 'catcast:ended_at'
    }
  }
]

