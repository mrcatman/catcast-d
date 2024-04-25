<template>
  <div class="page-container">
   <c-thumbs-list :config="getConfig">
      <template slot="item" slot-scope="props">
        <channel-thumb :data="props.item" />
      </template>
    </c-thumbs-list>
  </div>
</template>

<script>
  import ChannelThumb from "@/components/thumbs/ChannelThumb";
  export default {
    components: {
      ChannelThumb,
    },
    props: {
      title: {
        type: String,
        required: false
      },
      queryParams: {
        type: String,
        required: false,
        default: ''
      },
      url: {
        type: String,
        required: false,
        default: '/channels'
      },
      config: {
        type: Object,
        required: false,
        default: () => {
          return {}
        }
      }
    },
    computed: {
      getConfig() {
        return {
          title: this.title,
          url: this.getUrl,
          canChangeView: true,
          paginate: true,
          infiniteScroll: true,
          ...this.config,
        }
      },
      getUrl() {
        return `${this.url}?${this.queryParams}`;
      }
    },
  }
</script>
