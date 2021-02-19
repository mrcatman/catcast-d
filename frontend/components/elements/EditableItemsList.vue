<template>
    <div class="editable-list">
      <div class="editable-list__inner">
        <div class="editable-list__item" :key="$index" v-for="(item, $index) in val">
          <input class="editable-list__item__input" v-model="val[$index]" />
          <span @click="val.splice($index, 1)" class="editable-list__item__delete">
            <i class="material-icons">close</i>
          </span>
        </div>
        <div class="editable-list__add">
          <input @keyup="onKeyup" class="editable-list__input" v-model="itemToAdd" />
          <m-button :disabled="itemToAdd === ''" flat icon="post_add" @click="add()">{{$t('common.add')}}</m-button>
        </div>
      </div>
      <ErrorsContainer :warnings="warnings" :errors="errors" />
    </div>
</template>
<style lang="scss">
.editable-list {
  margin: 0 0 1em;

  &__inner {
    background: rgba(255, 255, 255, .05);
  }

  &__item {
    border-bottom: 1px solid rgba(255, 255, 255, .1);
    display: flex;
    padding: .5em;
    font-size: 1.25em;
    &__input {
      flex: 1;
      color: #fff;
      font: inherit;
      border: none;
      background: none;
    }
    &__delete {
      margin-left: auto;
      cursor: pointer;
      opacity: .75;
      transition: opacity .25s;
      &:hover {
        opacity: 1;
      }
    }
  }

  &__input {
    color: #fff;
    font: inherit;
    border: none;
    height: 2.125em;
    flex: 1;
    background: linear-gradient(11deg, hsla(0, 0%, 100%, .01), hsla(0, 0%, 100%, .11));
  }

  &__add {
    display: flex;
    align-items: center;
    padding: .5em;
  }

}

</style>
<script lang="ts">
import Vue, {PropType} from 'vue'
import ErrorsContainer from '~/components/elements/ErrorsContainer.vue'

export default Vue.extend({
  components: { ErrorsContainer },
  data() {
    return {
      val: this.value as Array<String>,
      itemToAdd: '',
    }
  },
  methods: {
    onKeyup(e) {
      if (e.code === 'Enter') {
        this.add();
      }
    },
    add() {
      if (this.itemToAdd !== '') {
        this.val.push(this.itemToAdd.split('\n')[0]);
        this.itemToAdd = '';
      }
    }
  },
  watch: {
    val(newVal) {
      this.$emit('input', newVal);
    },
    value(newVal) {
      this.val = newVal;
    }
  },
  props: {
    value: {
      type: Array as PropType<Array<String>>,
      required: true,
    },
    title: {
      type: String as PropType<String>,
      required: false,
    },
    warnings: {
      type: Array,
      required: false
    },
    errors: {
      type: Array,
      required: false
    }
  }
});
</script>
