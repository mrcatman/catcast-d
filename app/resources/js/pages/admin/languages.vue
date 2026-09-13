<template>
  <div class="admin-panel__languages">
    <div class="box box--with-header">
      <div class="box__inner">
        <div class="admin-panel__languages__inner">
          <div v-if="!invisible[key]" class="admin-panel__lang-editor-container" :key="key" v-for="(val, key) in langs">
            <div class="admin-panel__lang-editor-container__title">{{key}}<c-button v-if="key!== 'ru'" @click="copyStructure(key)">Copy structure from RU</c-button></div>
            <LangEditor v-model="langs[key]"/>
          </div>
        </div>
      </div>
      <div class="box__footer">
        <c-button @click="save()">{{$t('global.save')}}</c-button>
      </div>
    </div>


  </div>
</template>
<style lang="scss">
  .admin-panel {
    &__languages {
      max-height: calc(100% - 10em);
      width: 100%;
      display: flex;
      align-items: center;
      &__inner {
        width: 100%;
        display: flex;
      }
      .box {
        width: 100%;
      }
      .box__inner {
        overflow: scroll;
        max-height: calc(100vh - 12em);
      }
    }

    &__lang-editor-container {
      flex: 1;
      margin: 1em;
      &__title {
        height: 2.5em;
      }
    }
  }
</style>
<script>
  import LangEditor from '@/components/admin/LangEditor';
  export default {
    methods: {
      save() {
        this.$axios.post('admin/languages', {languages: this.langs}).then(res => {
          this.$store.commit('NEW_ALERT', res.data);
        })
      },
      startCopyingStructure(languageKey, newLang, oldLang, path = []) {
        let oldLangData = JSON.parse(JSON.stringify(oldLang));
        let newLangData = newLang;
        path.forEach(pathItem => {
          oldLangData = oldLangData[pathItem];
          if (!newLangData[pathItem]) {
            newLangData[pathItem] = {};
          }
          newLangData = newLangData[pathItem];
        });
        if (!oldLangData) {
          oldLangData = '';
        }
        if (typeof newLangData === 'string') {
          console.log('is string', newLangData, path);
          return;
        }
        Object.keys(oldLangData).forEach(key => {
          if (typeof oldLangData[key] === 'string') {
            if (!newLangData[key]) {
              newLangData[key] = oldLangData[key]
            }
          } else {
            if (Array.isArray(oldLangData[key])) {
              if (newLangData[key] === undefined) {
                newLangData[key] = [];
              }
              oldLangData[key].forEach((item, index) => {
                if (newLangData[key][index] === undefined) {
                  if (typeof oldLangData[key][index] === 'string') {
                    newLangData[key].push(oldLangData[key][index])
                  } else {
                    if (newLangData[key][index] === undefined) {
                      newLangData[key].push({});
                    }
                    Object.keys(oldLangData[key][index]).forEach(innerKey => {
                      if (newLangData[key][index][innerKey] === undefined) {
                        newLangData[key][index][innerKey] = oldLangData[key][index][innerKey];
                      }
                    })
                  }
                }
              })
            } else {
              let newPath = JSON.parse(JSON.stringify(path));
              newPath.push(key);
              //console.log('new path', newPath);
              newLang = this.startCopyingStructure(languageKey, newLang, oldLang, newPath)
              //console.log('lang after copy', JSON.stringify(newLang))
            }
          }
        });
        //_.set(newLang, path.join("."), newLangData);
        this.$set(this.invisible, languageKey, true);
        this.langs[languageKey] = newLang;

        this.$nextTick(() => {
          this.$set(this.invisible, languageKey, false);
        });
        return newLang;
      },
      copyStructure(lang) {
        let data = JSON.parse(JSON.stringify(this.langs[lang]));
        this.startCopyingStructure(lang, data, this.langs.ru, []);
      }
    },
    data() {
      return {
        invisible: {}
      }
    },
    components: {
      LangEditor
    },
    async asyncData({ app }) {
      let langs = (await app.$axios.post('/languages/gettexts')).data;
      return {
        langs
      }
    }
  }
</script>
