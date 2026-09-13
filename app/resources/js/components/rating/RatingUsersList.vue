<template>
  <c-thumbs-list ref="list" :config="listConfig">
    <template #item slot-scope="props">
      <c-list-item :to="`/users/${props.item.user.id}`" :picture="props.item.user.avatar" picture-square>
        <template #captions>
          <div class="list-item__title">{{ props.item.user.username }}</div>
        </template>
        <template #buttons>
              <span class="rating__list__weight" v-if="ratingEnableDislikes(entityType)" :class="{'rating__list__weight--positive': props.item.weight > 0,'rating__list__weight--negative': props.item.weight < 0}">
                {{ props.item.weight > 0 ? `+${props.item.weight}` : props.item.weight }}
              </span>
        </template>
      </c-list-item>
    </template>
  </c-thumbs-list>
</template>
<script lang="ts" setup>
//   computed: {
//     listConfig() {
//       return {
//         view: 'list',
//         url: `/likes/${this.entityType}/${this.entityId}/users`,
//         paginate: true,
//         infiniteScroll: true,
//         innerScroll: true,
//         noPadding: true
//       }
//     },
//   },

</script>
