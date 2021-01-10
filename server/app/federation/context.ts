export const context = [
  'https://www.w3.org/ns/activitystreams',
  'https://w3id.org/security/v1',
  {
    sc: 'http://schema.org#',
    catcastActorType: {
      '@type': 'sc:Text', '@id': 'catcast:catcastActorType'
    },
    catcastObjectType: {
      '@type': 'sc:Text', '@id': 'catcast:catcastObjectType'
    },
    broadcaster: {
      '@type': '@id', '@id': 'catcast:broadcaster'
    },
    channel: {
      '@type': '@id', '@id': 'catcast:channel'
    },
    endedAt: {
      '@type': 'sc:Time', '@id': 'catcast:endedAt'
    }
  }
]

