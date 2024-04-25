<script>
  /* eslint-disable */
  const validators = {
    email: new RegExp(
      /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    ),
    url: new RegExp(
      /^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i
    ),
    text: new RegExp(/^[a-zA-Z]+$/),
    digits: new RegExp(/^[\d() \.\:\-\+#]+$/),
    isodate: new RegExp(
      /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/
    )
  };
  /* eslint-enable */

  export default {
    name: "InputTag",

    props: {
      tags: {
        type: Array,
        default: () => []
      },
      placeholder: {
        type: String,
        default: ""
      },
      readOnly: {
        type: Boolean,
        default: false
      },
      validate: {
        type: String | Function | Object,
        default: ""
      },
      addTagOnKeys: {
        type: Array,
        default: function() {
          return [
            13, // Return
            188, // Comma ','
            9 // Tab
          ];
        }
      },
      addTagOnBlur: {
        type: Boolean,
        default: false
      },
      limit: {
        type: Number,
        default: -1
      },
      allowDuplicates: {
        type: Boolean,
        default: false
      },
      value: {
        type: Array,
        required: false,
      }
    },

    data() {
      return {
        newTag: "",
        innerTags: this.value ? [...this.value] : [...this.tags],
        isInputActive: false
      };
    },
    watch: {
      innerTags(tags) {
        this.$emit('input', tags);
      },
      value(newVal) {
        this.innerTags = newVal;
      },
    },

    computed: {
      isLimit: function() {
        return this.limit > 0 && Number(this.limit) === this.innerTags.length;
      }
    },

    methods: {
      focusNewTag() {
        if (this.readOnly || !this.$el.querySelector(".new-tag")) {
          return;
        }
        this.$el.querySelector(".new-tag").focus();
      },

      handleInputFocus() {
        this.isInputActive = true;
      },

      handleInputBlur(e) {
        this.isInputActive = false;
        this.addNew(e);
      },

      addNew(e) {
        const keyShouldAddTag = e
          ? this.addTagOnKeys.indexOf(e.keyCode) !== -1
          : true;

        const typeIsNotBlur = e && e.type !== "blur";

        if (
          (!keyShouldAddTag && (typeIsNotBlur || !this.addTagOnBlur)) ||
          this.isLimit
        ) {
          return;
        }

        if (
          this.newTag &&
          (this.allowDuplicates || this.innerTags.indexOf(this.newTag) === -1) &&
          this.validateIfNeeded(this.newTag)
        ) {
          this.innerTags.push(this.newTag);

          this.tagChange();
          this.$nextTick(() => {
            this.newTag = "";
          })
        }
      },

      validateIfNeeded(tagValue) {
        if (this.validate === "" || this.validate === undefined) {
          return true;
        }

        if (typeof this.validate === "function") {
          return this.validate(tagValue);
        }

        if (
          typeof this.validate === "string" &&
          Object.keys(validators).indexOf(this.validate) > -1
        ) {
          return validators[this.validate].test(tagValue);
        }

        if (
          typeof this.validate === "object" &&
          this.validate.test !== undefined
        ) {
          return this.validate.test(tagValue);
        }

        return true;
      },

      remove(index) {
        this.innerTags.splice(index, 1);
        this.tagChange();
      },

      removeLastTag() {
        if (this.newTag) {
          return;
        }
        this.innerTags.pop();
        this.tagChange();
      },

      tagChange() {
        this.$emit("update:tags", this.innerTags);
      }
    }
  };
</script>

<template>
  <div
    @click="focusNewTag()"
    :class="{
      'read-only': readOnly,
      'vue-input-tag-wrapper--active': isInputActive,
    }"
    class="vue-input-tag-wrapper"
  >
    <span v-for="(tag, index) in innerTags" :key="index" class="input-tag">
      <span>{{ tag }}</span>
      <a v-if="!readOnly" @click.prevent.stop="remove(index)" class="remove"></a>
    </span>
    <input
      v-if                     = "!readOnly && !isLimit"
      ref                      = "inputtag"
      :placeholder             = "placeholder"
      type                     = "text"
      v-model                  = "newTag"
      v-on:keydown.delete.stop = "removeLastTag"
      v-on:keydown             = "addNew"
      v-on:blur                = "handleInputBlur"
      v-on:focus               = "handleInputFocus"
      class                    = "new-tag"
    />
  </div>
</template>

<style>
  .vue-input-tag-wrapper {
    overflow: hidden;
    cursor: text;
    text-align: left;
    display: flex;
    flex-wrap: wrap;
  }

  .vue-input-tag-wrapper .input-tag {
    border-radius: 2px;
    display: inline-block;
    font-weight: 400;
  }

  .vue-input-tag-wrapper .input-tag .remove {
    cursor: pointer;
    font-weight: bold;
  }

  .vue-input-tag-wrapper .input-tag .remove:hover {
    text-decoration: none;
  }

  .vue-input-tag-wrapper .input-tag .remove::before {
    content: " x";
  }

  .vue-input-tag-wrapper .new-tag {
    background: transparent;
    border: 0;
    font-weight: 400;
    outline: none;
    padding: 4px;
    padding-left: 0;
    flex-grow: 1;
  }

  .vue-input-tag-wrapper.read-only {
    cursor: default;
  }
</style>
