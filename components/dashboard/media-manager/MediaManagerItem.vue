<template>
  <c-list-item class="media-manager__item"
               @click.native="onItemClick"
               :to="!config.disableLinks ? link : null"
               :class="classes"
               :length="item.object.length"
               :showPicturePlaceholder="item.object.type_id=== 'video'"
               :picture="item.object.thumbnail ? item.object.thumbnail.full_url : null"
               :icon="item.is_back || item.is_folder ? 'fa-folder' : null"
               :data-id="item.is_folder ? -1 * item.object.id : item.object.id"
              :data-can-edit="item.permissions?.can_edit ? 1 : 0"
        >
    <template slot="captions">
      <div class="list-item__title">
        <c-checkbox v-if="canSelect" v-model="isSelected" class="media-manager__item__select"/>
        {{ item.object.title }}
      </div>
      <div class="list-item__under-title">
        <channel-logo-and-name class="media-manager__item__channel" v-if="config.showChannel && item.object.channel" :channel="item.object.channel" />
        <c-statistics-icons :data="[
            {icon: 'fa-file', value: item.object.total_files_size ? bytesToFileSize(item.object.total_files_size) : null},
            {icon: item.object.type_id=== 'video' ? 'remove_red_eye' : 'headphones', value: item.object.views},
            {icon: 'thumb_up', value: item.object.likes_count},
            {icon: 'fa-clock', value: item.object.created_at ? formatPublishDate(item.object.created_at, false) : null}
        ]"></c-statistics-icons>
      </div>
    </template>
    <template slot="buttons" v-if="item.object.id && !config.disableEditing">
      <c-button transparent narrow icon-only icon="menu" >
        <c-popup-menu position="bottom-left" activate-on-parent-click>
          <c-popup-menu-item v-if="!item.is_folder" :to="item.object.local_url" icon="arrow_outward">{{ $t('global.link') }}</c-popup-menu-item>
          <c-popup-menu-item v-if="item.permissions?.can_view_statistics" :to="`${link}?t=statistics`" icon="show_chart">{{ $t('statistics.show') }}</c-popup-menu-item>
          <c-popup-menu-item v-if="item.permissions?.can_edit"  @click="(e) => onButtonClick(e,'edit')" :to="!item.is_folder ? link : null" icon="edit">{{ $t('global.edit') }}</c-popup-menu-item>
          <c-popup-menu-item v-if="item.permissions?.can_edit"  @click="(e) => onButtonClick(e,'delete')" :to="!item.is_folder ? link : null" icon="close">{{ $t('global.delete') }}</c-popup-menu-item>
        </c-popup-menu>
      </c-button>

    </template>
    <template slot="buttons" v-if="$slots.custom_buttons">
      <slot name="custom_buttons"></slot>
    </template>
  </c-list-item>
</template>
<script>
import {mapGetters} from 'vuex';
import {formatPublishDate} from '@/helpers/dates';
import {bytesToFileSize} from "@/helpers/file-size";
import ChannelLogoAndName from "@/components/ChannelLogoAndName.vue";

export default {
  components: {ChannelLogoAndName},
  watch: {
    isSelected(selected) {
      this.$emit('selected', selected);
    },
    selected(selected) {
      this.isSelected = selected;
    }
  },
  data() {
    return {
      isSelected: this.selected,
    }
  },
  computed: {
    canSelect() {
      return !this.item.is_back && !this.config.disableSelection && !(this.item.is_folder && this.config.disableFolderSelection) && this.item.permissions?.can_edit;
    },
    classes() {
      return [
        this.canSelect ? 'media-manager__item--selectable' : '',
        this.isSelected ? 'media-manager__item--selected' : '',
        !this.config.disableLinks || this.config.inModal ? 'media-manager__item--clickable' : ''
      ];
    },
    link() {
      if (this.item.is_back) {
        return '';
      }
      return this.item.is_folder ?
        (this.item.object.id ?
            {
              name: 'dashboard-id-media-folder',
              params: {folder_id: this.item.object.id, title: this.item.object.title}
            } :
            `/dashboard/${this.item.object.channel_id}/media`
        ) :
        `/dashboard/${this.item.object.channel_id}/media/${this.item.object.uuid}`;
    },
  },
  methods: {
    bytesToFileSize,
    onItemClick() {
      this.$emit('click', this.item);
    },
    onButtonClick(e, type) {
      e.stopPropagation();
      this.$emit(type);
    },
    formatPublishDate
  },
  props: {
    item: {
      type: Object,
      required: true
    },
    selected: Boolean,
    config: {
      type: Object,
      required: false,
      default() {
        return {}
      }
    },
  }
}
</script>
<style lang="scss">
.media-manager {
  &__item {
    user-select: none;
    cursor: pointer;
    &--clickable {

    }
    &--selected {
      background: var(--lighten-2);
    }

    &__select {
      position: absolute;
      top: -.25em;
      left: .75em;
      z-index: 10;
    }
    &__channel {
      font-size: .875em;
      margin-right: 1em;
    }
  }
}
</style>
