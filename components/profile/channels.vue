<template>
  <c-box no-padding>
    <template slot="title">{{$t('profile.channels')}}</template>
    <template slot="main">
      <c-list>
        <c-list-item v-for="item in channels" :key="item.channel.id" :to="'/'+item.channel.shortname" :picture="item.channel.logo" :pictureSquare="true">
          <template slot="captions">
            <div class="list-item__title">{{item.channel.name}}</div>
            <span v-if="item.position" class="list-item__text">
              {{item.position}}
            </span>
            <div class="list-item__under-title">
              <c-tag v-if="item.owner">
                <c-icon icon="fas-crown" />
                {{$t('channel_permissions.list.owner._title')}}
              </c-tag>
            </div>
          </template>
        </c-list-item>
        <c-nothing-found small v-if="channels.length === 0" />
      </c-list>
    </template>
  </c-box>
</template>
<script>
export default {
  props: {
    channels: {
      type: Array,
      required: true
    },
  }
}
</script>
