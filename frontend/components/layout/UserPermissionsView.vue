<template>
  <div>
    <m-list-item-sub>
      <span v-if="!item.full" class="tag" v-for="item in permissions.list" :key="item">
        {{$t('permissions.list.' + item.toLowerCase())}}
      </span>
      <span v-else class="tag">
        {{$t('permissions.full')}}
      </span>
    </m-list-item-sub>
    <m-list-item-sub v-if="permissions.comment">
      {{permissions.comment}}
    </m-list-item-sub>
  </div>
</template>
<script>
export default {
  props: {
    permissions: {
      type: Object,
      required: true
    }
  }
}
</script>
