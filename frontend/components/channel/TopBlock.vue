<template>
  <div class="channel-layout__top-block">
    <div class="channel-layout__logo" v-if="channel.logo" :style="{backgroundImage :'url('+channel.logo+')'}"></div>
    <div class="channel-layout__top-block__texts">
      <div class="channel-layout__title-container">
        <div class="channel-layout__title">
          {{channel.name}}
        </div>
        <div class="buttons-row">
          <subscribe-button entity-type="channels" :entity-id="channel.id" />
        </div>
      </div>
      <active-broadcast-display v-if="activeBroadcast" :channel="channel" :broadcast="activeBroadcast"  />
    </div>
  </div>
</template>
<style lang="scss">
.channel-layout {
  &__top-block {
    padding: 1em;
    display: flex;
    &__texts {
      flex: 1;
    }
  }
  &__logo {
    width: 4em;
    height: 4em;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    margin-top: 1em;
    margin-right: 1em;
  }

}
</style>
<script>
  import { mapState } from "vuex";

  import SubscribeButton from "@/components/subscriptions/SubscribeButton.vue";
  import Rating from "@/components/Rating.vue";
  import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";

  export default {
    components: {ActiveBroadcastDisplay, Rating, SubscribeButton},

    props: {
      channel: {
        type: Object,
        required: true,
      },
      activeBroadcast: {
        type: Object,
        required: true
      }
    }
  }
</script>
