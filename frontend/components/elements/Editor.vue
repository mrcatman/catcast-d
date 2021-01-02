<template>
<div class="editor-container">
  <div class="editor__title">{{title}}</div>
  <quill-editor
    v-model="content"
    ref="myQuillEditor"
    :options="editorOptions"
    @blur="onEditorBlur($event)"
    @focus="onEditorFocus($event)"
    @ready="onEditorReady($event)">
  </quill-editor>
  <div class="editor__errors" v-if="errors">
    <div class="editor__error" :key="$index" v-for="(error, $index) in errors">
      {{$t(error)}}
    </div>
  </div>
</div>
</template>
<style lang="scss">
.editor {
	&-container{
		margin: 0 0 1em;
  }
	&__title {
		margin: .5em 0;
    font-size: .75em;
    color: var(--text-sub-color);
	}
  &__errors {
    color: var(--negative-color);
    font-size: .75em;
    font-weight: 500;
    padding: .25em 0;
  }
}
.ql-toolbar.ql-snow {
    background: rgba(255, 255, 255, .25);
    color: #ccc!important;
	  border: none!important;
    .theme-default & {
      box-shadow: 0 5px 25px -5px rgba(0, 0, 0, 0.45);
    }
}
.ql-toolbar button {
    color: #fff!important;
}

.ql-stroke {
    stroke: #eee!important;
}

.ql-fill {
    fill: #eee!important;
}

.ql-picker-label {
    color: #eee;
}

.ql-container {
    border: none!important;
    background: rgba(255, 255, 255, .1);
}

.ql-container {font: inherit!important;}

.ql-snow.ql-toolbar button:hover, .ql-snow .ql-toolbar button:hover, .ql-snow.ql-toolbar button:focus, .ql-snow .ql-toolbar button:focus, .ql-snow.ql-toolbar button.ql-active, .ql-snow .ql-toolbar button.ql-active, .ql-snow.ql-toolbar .ql-picker-label:hover, .ql-snow .ql-toolbar .ql-picker-label:hover, .ql-snow.ql-toolbar .ql-picker-label.ql-active, .ql-snow .ql-toolbar .ql-picker-label.ql-active, .ql-snow.ql-toolbar .ql-picker-item:hover, .ql-snow .ql-toolbar .ql-picker-item:hover, .ql-snow.ql-toolbar .ql-picker-item.ql-selected, .ql-snow .ql-toolbar .ql-picker-item.ql-selected {
	color:#fff!important;
}
.bright .editor__title {
  color: #333;
}

.quill-editor {
  box-shadow: 0 0 1em rgba(0, 0, 0, 0.1);
}

</style>
<script>
import { quillEditor } from 'vue-quill-editor'


let defaultToolbar = [
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ 'header': 1 }, { 'header': 2 }],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    [{ 'script': 'sub' }, { 'script': 'super' }],
    [{ 'indent': '-1' }, { 'indent': '+1' }],
    [{ 'direction': 'rtl' }],
    [{ 'size': ['small', false, 'large', 'huge'] }],
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'align': [] }],
];

let simpleToolbar = [
  ['bold', 'italic', 'underline', 'strike'],
];

export default {
  components: {
    quillEditor
  },
	props:{
    simple: {
      type: Boolean,
      required: false
    },
    errors: {
      type: Array,
      required: false,
    },
		value:{
			required: true,
		},
		title:{
			type: String,
			required: true,
		}
	},
  watch: {
    value(newVal) {
      this.content = newVal;
    },
    content(newContent) {
      this.$emit('input', newContent);
    }
  },
	data () {
    return {
      content: this.value || "",
      editorContent: '',
      editorOptions: {
        placeholder: this.$t('editor.enter_text'),
        modules: {
          toolbar: this.simple ? simpleToolbar : defaultToolbar
        }
      }
    }
  },
  mounted() {

  },
  methods: {
    onEditorBlur(editor) {

    },
    onEditorFocus(editor) {
    },
    onEditorReady(editor) {

    },
    onEditorChange({ editor, html, text }) {
      this.$emit('input', html);
    }
  }
}
</script>
