<template>
  <input-base class="editor" :inputValue="val" :title="title" :errors="errorsList">
    <vue-simplemde :configs="editorConfig" v-model="editorContent" />
  </input-base>
</template>
<style lang="scss">
@import '~/assets/styles/editor.scss';
.editor {
	margin: 0 0 1em;
  .vue-simplemde {
    flex: 1;
  }
  .CodeMirror {
    color: var(--text-color);
    background: var(--input-bg-color);
    border: 1px solid var(--input-border-color);
    font-size: 1em;
  }
  .CodeMirror, .CodeMirror-scroll {
    min-height: 5em;
  }
  .editor-statusbar {
    display: none;
  }

  .editor-toolbar {
    background: var(--lighten-2);
    color: var(--text-color);
    border: none;
    font-size: unset;
    padding: 0 .5em;
    a {
      border: none;
      width: 1.875em;
      height: 1.875em;
      font-size: 1em;
      color: unset!important;
    }
  }


  .editor-toolbar a.active, .editor-toolbar a:hover {
    color: #fff !important;
    border: none;
    background: rgba(255, 255, 255, .1);
  }

  .editor-toolbar i.separator {
    border-color: #fff;
  }
}

</style>
<script>
import VueSimplemde from 'vue-simplemde'
import InputBase from '@/components/global/InputBase';

const editorConfig = {

  toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image'],
}

export default {
  computed: {
    errorsList() {
      return [...(this.errors ? this.errors : []), ...(this.formErrors ? this.formErrors : [])];
    },
  },
  components: {
    InputBase,
    VueSimplemde
  },
  props: {
    errors: Array,
    value: String,
    title: String,
  },

  watch: {
    value(newVal) {
      this.val = newVal;
    },
    val(val) {
      this.editorContent = val || '';
    },
    editorContent(newContent) {
      this.$emit('input', newContent);
    }
  },
  data() {
    return {
      editorContent: this.value || '',
      val: '',
      formErrors: [],
      editorConfig
    }
  },
}
</script>
