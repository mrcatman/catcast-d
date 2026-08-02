<template>
  <input-base class="editor" :inputValue="editorContent" :title="title" :errors="errors">
    <textarea ref="element"></textarea>
  </input-base>
</template>
<script lang="ts" setup>
import EasyMDE from 'easymde';

import InputBase from '@/components/ui/InputBase';

const editorConfig = {
  toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image'],
}

defineProps<{
  title: string,
  errors?: Array<string>,
}>();

const editorRef = useTemplateRef('element');
const editorContent = defineModel<string>();

let editor: EasyMDE;

onMounted(() => { // todo: maybe change to another markdown editor
  editor = new EasyMDE({
    element: editorRef.value as HTMLElement
  });
  editor.value(editorContent.value);
  editor.codemirror.on('change', (instance, changeObj) => {
    if (changeObj.origin === 'setValue') {
      return;
    }
    editorContent.value = editor.value();
  });
})

watch(editorContent, (value) => {
  console.log('changed', value);
  //editor && editor.value(value);
})
</script>

<style lang="scss">
@import '~/assets/styles/editor.scss';
.editor {
  .EasyMDEContainer {
    width: 100%;
  }
  .CodeMirror {
    color: var(--text-color);
    background: var(--input-bg-color);
    border: 1px solid var(--input-border-color);
    font-size: 1em;
  }
  .CodeMirror, .CodeMirror-scroll {
    min-height: 5em!important;
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
    button {
      cursor: pointer;
      border: none;
      background: none;
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
