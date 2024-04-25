<template>
  <div>
    <c-preloader v-if="loading"  />
    <c-form v-else :postData="data" method="post" url="tickets" :button-text="$t('global.send')" >
      <c-input v-form-input="'contacts'" v-if="!loggedIn" :title="$t('tickets.complaints.contacts')"   />
      <c-select v-form-input="'category'" :title="$t('tickets.complaints.category')" :options="categoriesList" />
      <c-input v-form-input="'text'" :title="$t('tickets.complaints.text')" type="textarea"  />
    </c-form>
  </div>

</template>
<script>
  import {mapState} from 'vuex';
  export default {
    computed: {
      ...mapState('auth', ['loggedIn']), // todo: complaints on backend
      categoriesList() {
        return this.categories.map(category => {
          return {
            name: this.$t(category.text),
            value: category.id
          }
        })
      }
    },
    data() {
      return {
        loading: false,
        categories: [],
        data: {
          contacts: '',
          category: 0,
          text: '',
          entity: this.entity,
          is_complaint: true
        },
      }
    },
    async mounted() {
      this.loading = true;
      this.categories = await this.$api.get('tickets/categories/complaints');
      this.loading = false;
    },
    props: {
      entity: {
        type: Object,
        required: true
      },
    }
  }
</script>
