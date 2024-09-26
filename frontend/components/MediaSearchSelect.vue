<template>
  <c-thumbs-list ref="list" :config="config" class="media-search-select">
    <template slot="item" slot-scope="props">
      <media-manager-item @click="setItemSelectionState(props.item.id, !selectedItemIds[props.item.id])" :config="itemsConfig" :item="{object: props.item}" :selected="selectedItemIds[props.item.id]" @selected="(e) => setItemSelectionState(props.item.id, e)"/>
    </template>
  </c-thumbs-list>
</template>
<style lang="scss">
.media-search-select {
  margin: -1em;
}
</style>
<script>
  import MediaManagerItem from "@/components/dashboard/media-manager/MediaManagerItem.vue";
  export default {
    components: {
      MediaManagerItem,
    },
    methods: {
      setItemSelectionState(id, state) {
        if (this.oneItem && state) {
          this.selectedItemIds = {};
        }
        this.$set(this.selectedItemIds, id, state);
      },
    },
    data() {
      return {
        selectedItemIds: {}
      }
    },
    computed: {
      selectedItems() {
        return this.$refs.list.list.data.filter(item => {
          return !!this.selectedItemIds[item.id];
        })
      },
      config() {
        return {
          url: `/media?type=${this.type}`,
          view: 'list',
          paginate: true,
          infiniteScroll: true,
          innerScroll: true,
          search: true
        }
      },
      itemsConfig() {
        return {
          disableLinks: true,
          disableEditing: true,
          showChannel: true
        }
      }
    },
    props: {
      type: {
        type: String,
        required: true
      },
      oneItem: {
        type: Boolean,
        required: false
      }
    }
  }
</script>
