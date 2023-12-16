<template>
  <div class="full-height">
    <div class="centered" v-if="loadingInitial">
      <c-preloader  />
    </div>
    <c-nothing-found v-else-if="goals.data.length === 0" :title="$t('global.nothing_found')">
      <div slot="buttons">
        <c-button :icon="'note_add'" @click="addNewGoal()">{{$t('dashboard.donates.goals.add_new')}}</c-button>
      </div>
    </c-nothing-found>
    <div v-else class="list-container">
     <c-infinite-scroll :loading="loading" @scroll="loadMore" class="list-container__inner">
        <div class="list-item list-item--not-link">
          <a @click="addNewGoal()" class="list-item__button">
            <span class="list-item__button__icon">
               <i class="material-icons">note_add</i>
             </span>
            <span class="list-item__button__text">
             {{$t('dashboard.donates.goals.add_new')}}
            </span>
          </a>
        </div>
        <div class="list-item list-item--not-link" :class="{'list-item--current': goal.is_active}" :key="$index" v-for="(goal, $index) in goals.data">
          <div class="list-item__left">
            <div class="list-item__captions">
              <div class="list-item__title">{{goal.title}}</div>
              <div class="list-item__under-title">{{goal.sum_collected_readable}} / {{goal.sum_readable}}</div>
            </div>
            <div class="list-item__progress">
              <div class="list-item__progress__bar" :style="{width: goal.percent + '%'}"></div>
            </div>
          </div>
          <div class="list-item__right">
            <div class="list-item__small-buttons">
              <a @click="makeActive(goal)" class="list-item__small-button" :class="{'list-item__small-button--active': goal.is_active}">
                <i class="material-icons">done</i>
                <span class="list-item__small-button__title">{{$t('dashboard.donates.goals.make_active')}}</span>
              </a>
              <a v-if="goal.can_edit" @click="editGoal(goal)" class="list-item__small-button">
                <i class="material-icons">edit</i>
                <span class="list-item__small-button__title">{{$t('dashboard.donates.goals.edit')}}</span>
              </a>
              <a v-if="goal.can_edit" @click="deleteGoal.id = goal.id; deleteGoal.modalVisible = true" class="list-item__small-button">
                <i class="material-icons">delete</i>
                <span class="list-item__small-button__title">{{$t('dashboard.donates.goals.delete')}}</span>
              </a>

            </div>
          </div>
        </div>
      </c-infinite-scroll>
    </div>
    <c-modal v-model="goalModalVisible" :header="isEditing ? $t('dashboard.donates.goals.edit') : $t('dashboard.donates.goals.add_new')">
      <div slot="main">
        <c-response :data="response"/>
        <div class="modal__input-container">
          <c-input :errors="errors.title" :placeholder="$t('dashboard.donates.goals.title')" v-model="goal.title" />
        </div>
        <div class="modal__input-container">
          <c-input type="textarea" :errors="errors.comment" :placeholder="$t('dashboard.donates.goals.comment')" v-model="goal.comment" />
        </div>
        <div class="modal__input-container">
          <c-input :errors="errors.button_text" :placeholder="$t('dashboard.donates.goals.button_text')" v-model="goal.button_text" />
        </div>
        <div class="modal__input-container">
          <c-input type="number" :min="1" :errors="errors.sum" :placeholder="$t('dashboard.donates.goals.sum')" v-model="goal.sum" />
        </div>
        <div class="modal__input-container">
          <c-checkbox :errors="errors.is_active" :title="$t('dashboard.donates.goals.is_active')" v-model="goal.is_active" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="saveGoal()" :loading="saving">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="goalModalVisible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>
    <c-modal v-model="deleteGoal.modalVisible" :header="$t('dashboard.donates.goals.delete')">
      <div slot="main">
        {{$t('dashboard.donates.goals.delete_confirm')}}
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="deleteSelectedGoal()" :loading="deleteGoal.loading">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="deleteGoal.ModalVisible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>
  </div>
</template>
<script>
  let count = 10;
  export default {
    components: {
    },
    async mounted() {
      this.goals = this.goalsList;
      this.loadingInitial = false;
    },
    data() {
      return {
        currentPage: 1,
        loading: false,
        loadingInitial: true,
        goals: {},
        isEditing: false,
        saving: false,
        editingID: null,
        goalModalVisible: false,
        goal: {
          sum: 1000,
          is_active: true,
        },
        errors: {

        },
        response: {
          status: -1,
          text: ''
        },
        deleteGoal: {
          modalVisible: false,
          id: null,
          loading: false,
        }
      }
    },
    props: {
      goalsList: {
        type: [Object, Array],
        required: false,
      },
      channel: {
        type: [Object],
        required: true
      }
    },
    methods: {
      deleteSelectedGoal() {
        this.deleteGoal.loading = true;
        this.$axios.delete('donates/goals/' + this.deleteGoal.id).then(res=>{
          this.deleteGoal.modalVisible = false;
          this.deleteGoal.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.goals.data = this.goals.data.filter(goal => goal.id !== this.deleteGoal.id);
          }
        })
      },
      makeActive(goal) {
        this.$axios.post('donates/goals/' + goal.id+'/makeactive').then(res=>{
          if (res.data.status) {
            this.goals.data.forEach((goal, index) => {
              this.goals.data[index].is_active = goal.id === res.data.goal.id;
            })
          } else {
            this.$store.commit('NEW_ALERT', res.data);
          }
        })
      },
      saveGoal() {
        let data = this.goal;
        data.channel_id = this.channel.id;
        this.saving = true;
        this.$axios({
          method: this.isEditing ? 'PATCH' : 'POST',
          url: this.isEditing ? 'donates/goals/' + this.editingID : 'donates/goals',
          data: data,
        }).then(res => {
          this.saving = false;
          this.errors = res.data.errors || {};
          this.response = res.data;
          if (res.data.status) {
            setTimeout(()=>{
              this.goalModalVisible = false;
            }, 3500)
            if (res.data.goal.is_active) {
              this.goals.data.forEach((goal, index) => {
                this.goals.data[index].is_active = goal.id === res.data.goal.id;
              })
            }
            if (!this.isEditing) {
              this.goals.data = [res.data.goal, ...this.goals.data];
            } else {
              this.goals.data.forEach((goal, index) => {
                if (goal.id === res.data.goal.id) {
                  this.$set(this.goals.data, index, res.data.goal);
                }
              })
            }
          }
        })
      },
      editGoal(goal) {
        this.response = null;
        this.isEditing = true;
        this.goal = JSON.parse(JSON.stringify(goal));
        this.editingID = goal.id;
        this.goalModalVisible = true;
      },
      addNewGoal() {
        this.response = null;
        this.goal = {
          sum: 1000,
          is_active: true,
        };
        this.isEditing = false;
        this.goalModalVisible = true;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.goals.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(`donates/goals/getbychannel/${this.channel.id}?count=${count}&page=${this.currentPage}`).then(res => {
              this.goals.data = [...this.goals.data, ...res.data.list.data];
              this.loading = false;
            })
          }
        }
      }
    }
  }
</script>

