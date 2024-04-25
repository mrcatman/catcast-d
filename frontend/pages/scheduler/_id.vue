n<template>
  <div class="scheduler__container">
    <div class="scheduler__outer" v-if="!error">
      <div  class="scheduler__timetable-container__outer" :class="{'scheduler__timetable-container__outer--with-next-items': nextItems.length > 0}" v-if="timetableView">
        <div :style="{height: timetableHeight + '%', top: timetableTop + 'px'}" class="scheduler__timetable-container">
          <div class="scheduler__hours">
            <div @mousedown="onHoursResize" @mousewheel="onHoursResizeMousewheel" class="scheduler__hours__list" ref="hoursList" :class="hoursListClass">
              <div class="scheduler__hour" :key="$index" v-for="(hour, $index) in getHours">{{hour.text}}</div>
            </div>
          </div>
          <div class="scheduler__days">
            <div class="scheduler__day" :key="$index" v-for="(day, $index) in days">
              <div class="scheduler__day__text">{{day.text}}</div>
              <div class="scheduler__day__playlists">
                <div class="scheduler__playlist" :data-playlist-id="playlist.id" @click="editPlaylist(playlist)" :key="`playlist_${$index2}`" v-for="(playlist, $index2) in playlistsForDays[$index]" :style="playlist._style">
                  <div class="scheduler__playlist__dates" v-if="playlist._formattedDates">
                    <span class="scheduler__playlist__date">{{playlist._formattedDates.start}}</span> - <span class="scheduler__playlist__date">{{playlist._formattedDates.end}}</span>
                  </div>
                  <div class="scheduler__playlist__title">{{playlist.title}}</div>
                </div>
                <div class="scheduler__playlist scheduler__playlist--announce" @click="editAnnounce(announce)" :key="`announce_${$index2}`" v-for="(announce, $index2) in getAnnouncesForDay(day)" :style="announce._style">
                  <div class="scheduler__playlist__dates" v-if="announce._formattedDates">
                    <span class="scheduler__playlist__date">{{announce._formattedDates.start}}</span> - <span class="scheduler__playlist__date">{{announce._formattedDates.end}}</span>
                  </div>
                  <div class="scheduler__playlist__title">{{announce.title}}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="scheduler__classic-view"  :class="{'scheduler__classic-view--with-next-items': nextItems.length > 0}"  v-else>
        <VideoMetadataEditor @close="() => playlistItemEditPanel.visible = false" :visible="playlistItemEditPanel.visible" v-model="playlistItemEditPanel.data" @save="savePlaylistItem"/>
        <ResizableRow @resizestart="isResizing = true" @resizeend="isResizing = false" name="tracks_page_row">
          <ResizableRowChild :defaultWidth="0.55">
            <div class="scheduler__classic-view__media-manager">
              <MediaManager :inScheduler="true" :channel="channel" />
            </div>
          </ResizableRowChild>
          <ResizableRowBar/>
          <ResizableRowChild :defaultWidth="0.45">
            <div class="scheduler__classic-view__playlists">
              <div class="scheduler__classic-view__tabs">
                <c-tabs :data="tabs" v-model="currentTab"/>
              </div>
              <div class="scheduler__classic-view__playlists__inner" v-show="currentTab === 'playlists'">
                <div v-if="playlist.extended_data" class="scheduler__classic-view__playlist" :key="$index" v-for="(playlist, $index) in schedule.playlists">
                  <div :style="{backgroundColor: playlist.extended_data.color}" class="scheduler__classic-view__playlist__header" :class="{'scheduler__classic-view__playlist__header--dark': isDarkColor(playlist.extended_data.color)}">
                    <a @click="editPlaylist(playlist)" class="scheduler__classic-view__playlist__header__button">
                      <i class="fa fa-edit"></i>
                    </a>
                    <div class="scheduler__classic-view__playlist__title">{{playlist.extended_data.title}}</div>
                    <a @click="deletePlaylist(playlist)" class="scheduler__classic-view__playlist__header__button">
                      <i class="fa fa-times"></i>
                    </a>
                  </div>
                  <div :data-id="playlist.id" :data-index="$index" v-dragula="playlist.items" drake="main" :style="{borderColor: playlist.extended_data.color}" class="scheduler__classic-view__playlist__inner">
                    <div v-if="item" :key="item.id" v-for="(item, $index2) in playlist.items" class="scheduler__classic-view__playlist__item">
                      <div class="scheduler__classic-view__playlist__item__inner">
                        <a @click="openPlaylistItemEditPanel(item, $index, $index2)" class="scheduler__classic-view__playlist__item__button">
                          <i class="fa fa-edit"></i>
                        </a>
                        <span class="scheduler__classic-view__playlist__item__title">{{item.title}}</span>
                        <span class="scheduler__classic-view__playlist__item__time">
                          {{item._formattedTime}}
                        </span>
                        <a @click="deletePlaylistItem($index, $index2)" class="scheduler__classic-view__playlist__item__button">
                          <i class="fa fa-times"></i>
                        </a>
                      </div>
                      <div  :data-folder-index="$index2"  :data-playlist-index="$index" :data-playlist-id="playlist.id"  :ref="`folder${item.video_folder_id}`" class="scheduler__classic-view__playlist__item__folder-contents" v-if="item && item.is_folder" v-dragula="item.items" drake="main">
                        <div v-if="folderItem" :key="folderItem.video_id ? folderItem.video_id+'_'+$index3 : folderItem.id+'_'+$index3" v-for="(folderItem, $index3) in item.items" class="scheduler__classic-view__playlist__item">
                          <div class="scheduler__classic-view__playlist__item__inner">
                            <a @click="openPlaylistItemEditPanel(item, $index, $index2, $index3)" class="scheduler__classic-view__playlist__item__button">
                              <i class="fa fa-edit"></i>
                            </a>
                            <span class="scheduler__classic-view__playlist__item__title">{{folderItem.title}}</span>
                            <span class="scheduler__classic-view__playlist__item__time">
                              {{folderItem._formattedTime}}
                            </span>
                            <a @click="deletePlaylistItem($index, $index2, $index3)" class="scheduler__classic-view__playlist__item__button">
                              <i class="fa fa-times"></i>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="scheduler__classic-view__announces" v-show="currentTab === 'announces'">
                <AnnouncesManager @deleted="onAnnounceDeleted" :broadcastingUsers="broadcastingUsers"  :playlistsList="playlistsList" :isChannelAdmin="isChannelAdmin" :canAddAnnounces="canAddAnnounces" :channel="channel" v-model="schedule.announces"/>
              </div>

              <div class="scheduler__classic-view__playlists__bottom-panel">
                <c-button  icon="save" @click="savePlaylists()" color="green">{{$t('global.save')}}</c-button>
                <span v-if="playlistsToSave.items.length > 0" class="scheduler__classic-view__playlists__bottom-panel__text">{{$t('dashboard.tracks.unsaved_changes')}}</span>
                <c-preloader v-if="playlistsToSave.state"></c-preloader>
                <div v-if="playlistsToSave.state" class="scheduler__classic-view__loading-bar">
                  <div class="scheduler__classic-view__loading-bar__inner" :style="{width: playlistsToSave.percent * 100 + '%'}"></div>
                </div>
              </div>
            </div>
          </ResizableRowChild>
        </ResizableRow>
      </div>
      <div class="scheduler__next-items" v-show="nextItems.length > 0">
        <div class="scheduler__next-items__title">{{$t('scheduler.next_on_channel')}}</div>
        <div class="scheduler__next-items__list">
          <div v-for="(item, $index) in nextItems" :key="$index" class="scheduler__next-item">
            <div class="scheduler__next-item__time">{{getTime(item.time_start)}}</div>
            <div class="scheduler__next-item__title">{{item.title}}</div>
          </div>
        </div>
      </div>
        <div class="box box--with-header scheduler__bottom-panel-outer">
          <div class="box__footer">
            <div class="scheduler__bottom-panel__header-inner">
              <div class="scheduler__bottom-panel__dates__container">
                <div v-show="timetableView" class="scheduler__bottom-panel__dates">
                  <c-button flat icon-only icon="chevron_left" @click="changeWeek(-1)" />
                  <span class="scheduler__bottom-panel__dates__week">{{currentWeekIntervalText}}</span>
                  <c-button flat icon-only icon="chevron_right" @click="changeWeek(1)" />
                </div>
              </div>
              <div class="buttons-row">
                <c-button flat @click="timetableView = !timetableView">{{timetableView ? $t('scheduler.classic_view') : $t('scheduler.timetable_view')}}</c-button>
                <c-button v-if="canAddAnnounces" @click="openAnnouncePanel()" color="green" icon="add_box">{{$t('scheduler.add_stream_announce')}}</c-button>
                <c-button v-if="canAddPlaylists" @click="openPlaylistPanel()" icon="playlist_add_check">{{$t('scheduler.add_autopilot_playlist')}}</c-button>
              </div>
            </div>
          </div>
          <!--<div class="box__inner scheduler__bottom-panel-container">
            <MediaManager :canAdd="false" :filterServerOnly="true" :channel="channel" />
          </div>-->
        </div>

        <announcePanel @close="() => announcePanel.visible = false" :isEditing="announcePanel.isEditing" :broadcastingUsers="broadcastingUsers" :playlistsList="playlistsList" :isChannelAdmin="isChannelAdmin" :canAddAnnounces="canAddAnnounces" :visible="announcePanel.visible" @save="onAnnounceSaved" @deleted="onAnnounceDeleted" :channel="channel" v-model="announcePanel.data"/>

        <playlistEditor @close="(e) => {playlistPanel.visible = false}" :visible="playlistPanel.visible" @saved="onPlaylistSaved" @deleted="onPlaylistDeleted" :channel="channel" v-model="currentPlaylist"/>

        <c-modal v-model="deletePlaylistPanel.visible">
          <div slot="main" >
            <div class="modal__text">
              {{$t('scheduler.delete_playlist_text')}}
            </div>
          </div>
          <div class="modal__buttons" slot="buttons">
            <div class="buttons-row">
              <c-button @click="continueDeletingPlaylist()" :loading="deletePlaylistPanel.loading">{{$t('global.ok')}}</c-button>
              <c-button flat @click="deletePlaylistPanel.visible = false">{{$t('global.cancel')}}</c-button>
            </div>
          </div>
        </c-modal>
    </div>
    <c-error-page v-else :data="error" />
  </div>
</template>

<script>
  import { startOfWeek, startOfDay, format, addDays } from 'date-fns';
  import { getTime, formatFullDate, getDate, formatDuration } from '@/helpers/dates.js';
  import ResizableRow from '@/components/global/resizable/ResizableRow';
  import ResizableRowChild from '@/components/global/resizable/ResizableRowChild';
  import ResizableRowBar from '@/components/global/resizable/ResizableRowBar';
  import MediaManager from '@/components/dashboard/MediaManager';
  import playlistEditor from '@/components/scheduler/PlaylistEditor';
  import VideoMetadataEditor from '@/components/scheduler/VideoMetadataEditor';
  import announcePanel from '@/components/scheduler/announcePanel';
  import AnnouncesManager from "@/components/scheduler/AnnouncesManager";

  async function asyncForEach(array, callback) {
    for (let index = 0; index < array.length; index++) {
      await callback(array[index], index, array)
    }
  }

  let defaultAnnounceData = {
    length: 30,
    time: Math.floor(new Date().getTime() / 1000),
    title: '',
    description: '',
    user_id: null,
    cover: null,
    tags: [],
  };

  export default {
    middleware: 'auth',
    head () {
      return {
        title: this.$t('scheduler.heading'),
      }
    },
    beforeDestroy() {
      clearInterval(this.nextItemsInterval);
      if (this.$dragula.$service.drakes.main) {
        delete this.$dragula.$service.drakes.main;
      }
    },
    created() {
     // return null;
      const service = this.$dragula.$service;
      service.eventBus.$on('drop', (args) => {
          console.log('drop', args);
          console.log(this.$dragula.$service);
          const sourceIsMediaList = args.source.classList.contains('media-manager__items');
          const targetIsMediaList = args.container.classList.contains('media-manager__items');
          const el = args.el;
          if (sourceIsMediaList && !targetIsMediaList) {
              const index = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
              let videoData = JSON.parse(el.dataset.info);
              //  videoData.id = videoData.video_id;
              // delete videoData.video_id;
              let targetPlaylistIndex = null;
              if (el.parentNode) {
                  el.parentNode.removeChild(el);
              } else {
                  el.remove();
              }
              console.log('video data', videoData);
              //targetPlaylistId = parseInt(args.container.dataset.playlistId);
              if (args.container.dataset.folderIndex) {
                  let targetFolderIndex = parseInt(args.container.dataset.folderIndex);
                  targetPlaylistIndex = parseInt(args.container.dataset.playlistIndex);

                  this.schedule.playlists[targetPlaylistIndex].items[targetFolderIndex].items.splice(index, 0, videoData);
              } else {
                  targetPlaylistIndex = parseInt(args.container.dataset.index);
                  if (targetPlaylistIndex || targetPlaylistIndex === 0) {
                      this.schedule.playlists[targetPlaylistIndex].items.splice(index, 0, videoData);
                  }
              }
              if (videoData.is_folder) {
                  let playlist = this.schedule.playlists[targetPlaylistIndex];
                  if (playlist && playlist.extended_data && playlist.extended_data.type === "default") {
                      videoData._id = Math.ceil(Math.random() * 10000);
                      this.setFolderData(videoData);
                  }
              }
              ///return {
              //  video_id: item.id,
              //  length: item.length,
              //  title: item.title,
              //};

          }
      });
      service.eventBus.$on('dragend', (e) => {
        this.$nextTick(() => {
          this.setItemsTime();
        })
      });
      service.eventBus.$on('out', (args) => {
        let sourceID = parseInt(args.source.dataset.id);
        let targetID = parseInt(args.container.dataset.id);
        if (sourceID && this.playlistsToSave.items.indexOf(sourceID) === -1) {
          this.playlistsToSave.items.push(sourceID);
        }
        if (targetID && this.playlistsToSave.items.indexOf(targetID) === -1) {
          this.playlistsToSave.items.push(targetID);
        }
      });
      service.options('main', {
        copy: function(el,source) {
            console.log(source);
          const sourceIsMediaList = source.classList.contains('media-manager__items');
          return sourceIsMediaList;
        },
        accepts: function (el, target, source, sibling) {

          const sourceIsMediaList = source.classList.contains('media-manager__items');
          const targetIsMediaList = target.classList.contains('media-manager__items');
     //     console.log(target.classList, source.classList, sourceIsMediaList, targetIsMediaList);
          if (sourceIsMediaList && !targetIsMediaList) {
            return true;
          }
          if (!sourceIsMediaList && !targetIsMediaList) {
            return true;
          }
          return false;
        },
        revertOnSpill: true
      })
    },
    watch: {
      timetableView(newView) {
        localStorage.scheduler_timetable_view = newView;
      }
    },
    computed: {
      tabs() {
        return [
          {id: 'playlists', name: this.$t('scheduler.tabs.playlists')},
          {id: 'announces', name: this.$t('scheduler.tabs.announces')},
        ]
      },
      projectsList() {
        let data = this.projects.map(project => {
          return {
            name: project.name,
            value: project.id
          }
        });
        data.unshift({
          name: '...',
          value: null
        })
        return data;
      },
      canAddPlaylists() {
        let canAdd = false;
        ['channel_admin', 'admin', 'owner', 'autopilot'].forEach(key => {
          if (this.permissions.indexOf(key) !== -1) {
            canAdd = true;
          }
        });
        return canAdd;
      },
      canAddAnnounces() {
        let canAdd = false;
        ['channel_admin', 'admin', 'owner', 'live_broadcast'].forEach(key => {
          if (this.permissions.indexOf(key) !== -1) {
            canAdd = true;
          }
        });
        return canAdd;
      },
      showEditPanel() {
        return this.currentPlaylist || this.announcePanel.visible;
      },
      isChannelAdmin() {
        let isAdmin = false;
        ['channel_admin', 'admin', 'owner'].forEach(key => {
          if (this.permissions.indexOf(key) !== -1) {
            isAdmin = true;
          }
        });
        return isAdmin;
      },
      getHours() {
        let hours = [];
        for (let i = 0; i < 24; i++) {
          hours.push({
            num: i,
            text: (i<10 ? '0' + i : i)+':00'
          })
        }
        return hours;
      }
    },
    methods: {
      setNextItemsInterval() {
        clearInterval(this.nextItemsInterval);
        this.nextItemsInterval = setInterval(() => {
          this.getNextItems();
        }, 1000 * 60 * 5);
        this.getNextItems();
      },
      getNextItems() {
        this.$axios.get(`timetable/next/${this.channel.id}?count=3`).then(res => {
          this.nextItems = res.data.data.next;
        })
      },
      onAnnounceDeleted(deletedItem) {
        this.schedule.announces = this.schedule.announces.filter(announce => announce.id !== deletedItem.id);
      },
      onAnnounceSaved(newAnnounce) {
        if (this.announcePanel.isEditing) {
          this.schedule.announces = this.schedule.announces.filter(announce => announce.id !== newAnnounce.id);
        }
        this.schedule.announces.unshift(newAnnounce);
      },
      formatFullDate,
      continueDeletingPlaylist() {
        this.deletePlaylistPanel.loading = true;
        this.$axios.delete(`timetable/playlists/${this.deletePlaylistPanel.data.id}`).then (res => {
          this.deletePlaylistPanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.deletePlaylistPanel.visible = false;
            this.schedule.playlists = this.schedule.playlists.filter(playlist => playlist.id !== this.deletePlaylistPanel.data.id);
            this.setNextItemsInterval();
          }
        })
      },
      deletePlaylist(playlist) {
        this.deletePlaylistPanel.data = playlist;
        this.deletePlaylistPanel.visible = true;
      },
      savePlaylists() {
        let i = 0;
        let has_errors = false;
        let playlists = this.schedule.playlists.filter(playlist => this.playlistsToSave.items.indexOf(playlist.id) !== -1);
        asyncForEach(playlists, async (playlist) => {
          this.playlistsToSave.state = true;
          if (!has_errors) {
            const res = await this.$axios.put('/timetable/playlists/' + playlist.id, playlist);
            if (res.data.status) {
              this.playlistsToSave.percent = (i + 1) / playlists.length;
              i++;
              if (i === playlists.length) {
                this.playlistsToSave.items = [];
                this.playlistsToSave.state = false;
                this.setNextItemsInterval();
              }
            } else {
              this.playlistsToSave.state = false;
              this.$store.commit('NEW_ALERT', res.data);
              has_errors = true;
            }
          }
        });
      },
      setItemsTime() {
        this.$nextTick(() => {
          this.schedule.playlists.forEach(playlist => {
            if (!playlist.extended_data) {
              return;
            }
            let items = playlist.items;
            let time = playlist.extended_data.time_start;
            items.forEach(item => {
              if (item) {

                if (!item.is_folder) {
                  let length = parseInt(item.length);
                  if (playlist.extended_data.playback_type === "default") {
                    this.$set(item, '_formattedTime',getTime(time) + "-" + getTime(time + length));
                  } else {
                    this.$set(item, '_formattedTime',this.getLength(item));
                  }
                  time += length;
                } else {
                  if (!item.items) {
                    return;
                  }
                  item.items.forEach(folderItem => {
                    if (folderItem) {
                      let length = parseInt(folderItem.length);
                      if (playlist.extended_data.playback_type === "default") {
                        this.$set(folderItem, '_formattedTime', getTime(time) + "-" + getTime(time + length));
                      } else {
                        this.$set(folderItem, '_formattedTime', this.getLength(folderItem));
                      }
                      time += length;
                    }
                  });
                }
              }
            });
            return items;
          })
        })
      },
      savePlaylistItem(data) {
        let items = this.schedule.playlists[this.playlistItemEditPanel.playlistEditIndex].items;
        if (this.playlistItemEditPanel.folderEditIndex && this.playlistItemEditPanel.folderEditIndex > -1) {
          items = items[this.playlistItemEditPanel.folderEditIndex].items;
        }
        this.$set(items, this.playlistItemEditPanel.editIndex,  data);
        this.playlistItemEditPanel.visible = false;
      },
      deletePlaylistItem(playlistIndex, itemIndex, folderIndex = null) {
        this.playlistsToSave.items.push(this.schedule.playlists[playlistIndex].id);
        if (folderIndex !== null) {
          if (this.schedule.playlists[playlistIndex].items[itemIndex]) {
            this.schedule.playlists[playlistIndex].items[itemIndex].items.splice(folderIndex, 1);
          }
        } else {
          this.schedule.playlists[playlistIndex].items.splice(itemIndex, 1);
        }
      },
      openPlaylistItemEditPanel(item, playlistIndex, itemIndex, folderIndex = null) {
        this.playlistItemEditPanel.playlistEditIndex = playlistIndex;
        this.playlistItemEditPanel.editIndex = itemIndex;
        if (folderIndex !== null) {
          this.playlistItemEditPanel.folderEditIndex = folderIndex;
        }
        this.playlistItemEditPanel.data = JSON.parse(JSON.stringify(item));
        this.playlistItemEditPanel.visible = true;
      },
      isDarkColor(colorValue) {
        if (!colorValue) {
          return true;
        }
        let color = +("0x" + colorValue.slice(1).replace(colorValue.length < 5 && /./g, '$&$&'));
        let r = color >> 16;
        let g = color >> 8 & 255;
        let b = color & 255;
        let hsp = Math.sqrt(
          0.299 * (r * r) +
          0.587 * (g * g) +
          0.114 * (b * b)
        );
        return hsp <= 130;
      },
      onHoursResizeMousewheel(e) {
        this.timetableHeight += e.deltaY / 20;
        this.onResize();
      },
      onHoursResize(e) {
        const startY = e.clientY;
        let startPos = this.timetableTop;
        const onMouseMove = (e) => {
          const diff = startY - e.clientY;
          this.timetableTop = startPos - diff;
        };
        onMouseMove(e);
        window.addEventListener('mousemove', onMouseMove)
        window.addEventListener('mouseup', () => {
          window.removeEventListener('mousemove', onMouseMove);
        })
      },
      onPlaylistDeleted({id}) {
        this.playlistPanel.visible = false;
        this.schedule.playlists = this.schedule.playlists.filter(playlist => playlist.id !== id);
        this.setPlaylistsForDays();
        this.setNextItemsInterval();
      },
      onPlaylistSaved({isEditing, playlist}) {
        this.playlistPanel.visible = false;
        if (!isEditing) {
          this.schedule.playlists.push(playlist);
        } else {
          this.schedule.playlists.forEach((playlistItem, index) => {
            if (playlist.id === playlistItem.id) {
              this.$set(this.schedule.playlists, index, playlist);
            }
          })
        }
        this.setPlaylistsForDays();
        this.setNextItemsInterval();
      },
      async setFolderData(folder) {
        let data = {};

        if (!folder.is_project) {
          data.folder_id = folder.video_folder_id;
        } else {
         data.project_id = -1 * folder.video_folder_id;
        }
        let res = (await this.$axios.post(`videos/filemanager/${this.channel.id}`, data)).data;
        if (res.status) {
          let folderData = null;
          if (this.timetableView) {
            folderData = this.currentPlaylist.items.filter(item => item._id === folder._id)[0];
          } else {
            this.schedule.playlists.forEach(playlist => {
              if (playlist.items) {
                playlist.items.forEach(item => {
                  if (item && item.is_folder && item._id === folder._id) {
                    folderData = item;
                  }
                })
              }
            })
          }
          if (folderData) {
            this.$set(folderData, 'items', res.data.videos.filter(video => video.is_on_server).map(video => { //
              return {
                video_id: video.id,
                title: video.title,
                length: video.length
              };
            }));
            let ref = this.$refs['folder' + folder.video_folder_id];
            if (ref && ref[0]) {
              ref = ref[0];
            }
            this.$dragula.$service.drakes.main.models.push({
              container: ref,
              model: folderData.items,
            });
            this.$nextTick(() => {
              this.setItemsTime();
            })
          }
        }
      },
      setDays() {
        let date = this.currentWeekStart;
        let endDate = addDays(new Date(date.getTime() - 1), 7);
        this.currentWeekIntervalText = format(date, 'D.M.YY')+' - '+format(endDate, 'D.M.YY');
        this.days = [];
        for (let i = 0; i <= 6; i++) {
          let date = addDays(this.currentWeekStart, i);
          let day = {
            text: getDate(date),
            ts: Math.floor(startOfDay(date).getTime() / 1000),
          };
          this.days.push(day);
        }
        this.setPlaylistsForDays();
      },
      changeWeek(index) {
        this.currentWeekStart = addDays(this.currentWeekStart, 7 * index);
        this.setDays();
      },

      onResize() {
        let hours = this.$refs.hoursList;
        if (!hours) {
          return;
        }
        let rect = hours.getBoundingClientRect();
        if (rect.width > 1000) {
          this.hoursListClass = '';
        } else {
          this.hoursListClass = 'scheduler__hours__list--small';
        }
      },
      editAnnounce(announce) {
        if (this.canAddAnnounces) {
          this.announcePanel.isEditing = true;
          let data = JSON.parse(JSON.stringify(announce));
          if (announce.pictures_data && announce.pictures_data.cover) {
            this.announcePanel.coverData = announce.pictures_data.cover;
            data.cover = announce.pictures_data.cover.id;
          }
          this.announcePanel.data = data;
          this.announcePanel.visible = true;
        }
      },
      openPlaylistPanel() {
        this.currentTab = 'playlists';
        let playlist = {
          time_start: new Date().getTime() + 60,
          items: [],
          data: {
            type: 'default',
            playback_type: 'default',
            cycled: {
              status: false,
            }
          }
        };
        this.schedule.playlists.push(playlist);
        this.currentPlaylist = playlist;
        this.playlistPanel.visible = true;
        this.$nextTick(() => {
          this.onResize();
        })
      },
      openAnnouncePanel() {
        this.currentTab = 'announces';
        this.announcePanel.isEditing = false;
        this.announcePanel.data = JSON.parse(JSON.stringify(defaultAnnounceData));
        this.announcePanel.visible = true;

      },
      getTime,
      getLength(item, index) {
        if (!this.showItemDates) {
          if (item.is_folder) {
            let length = 0;
            if (item.items) {
              item.items.forEach(video => {
                if (video && video.length) {
                  length += parseInt(video.length);
                }
              })
            }
            return formatDuration(length);
          }
          return formatDuration(item.length);
        }
        return "";
      },
      editPlaylist(playlist) {
        this.currentPlaylist = JSON.parse(JSON.stringify(playlist));
        if (!this.currentPlaylist.data.type) {
          this.currentPlaylist.data.type = 'default';
        }
        this.playlistPanel.visible = true;
      },
      getPlaylistEnd(playlist) {
        if (playlist.data.cycled.status) {
          return playlist.data.cycled.till;
        }
        let time = parseInt(playlist.data.time_start);
        playlist.items.forEach(item => {
          if (item) {
            if (item.length) {
              time += parseInt(item.length);
            } else {
              if (item.is_folder && item.items) {
                item.items.forEach(folderItem => {
                  time += parseInt(folderItem.length);
                })
              }
            }
          }
        });

        return time;
      },
      getAnnouncesForDay(day) {
        const seconds = 86400;
        let dayStart = day.ts;
        let dayEnd = day.ts + seconds;
        let announces = [];
        const list = JSON.parse(JSON.stringify(this.schedule.announces));
        list.forEach(announce => {
          const start = announce.time;
          const end = announce.time + (announce.length * 60);
          announce._formattedDates = {
            start: getTime(start),
            end: getTime(end)
          };
          if (start < dayEnd && end > dayStart) {
            let left = 0;
            if (start < dayEnd && start > dayStart) {
              left = ((start - dayStart) / seconds * 100);
            }
            let height = 100 - left;
            if (end < dayEnd && end > dayStart) {
              height = ((end - start) / seconds * 100);
            }
            announce._style = {
              top: left + '%',
              height: `calc(${height}% - .25em)`
            };
            announces.push(announce);
          }
        });
        return announces;
      },
      getMinutesFromText(text) {
        if (!text) {
          return false;
        }
        let splitted = text.split(":");
        return parseInt(splitted[0]) * 60 + parseInt(splitted[1]);
      },
      getRandomLength(playlist) {
        let length = 0 ;
        for (let i = 0; i < playlist.extended_data.playback_limit.value; i++) {
          let randomItem = playlist.items[Math.floor(Math.random() * playlist.items.length)];
          length += parseInt(randomItem.length);
        }
        return length;
      },
      setPlaylistsForDays() {
        let playlistsForDays = {};
        this.days.forEach((day, index) => {
          playlistsForDays[index] = [];
        });
        const seconds = 86400;
        const list = JSON.parse(JSON.stringify(this.schedule.playlists));
        let aroundTheClockPlaylist = list.filter(playlist => playlist && playlist.extended_data && playlist.extended_data.playback_type === "around_the_clock")[0];
        this.days.forEach((day, index) => {
          let dayIndex = (new Date(day.ts * 1000)).getDay() + 1;
          let dayStart = day.ts;
          let dayEnd = day.ts + seconds;
          list.forEach(playlistItem => {
            if (playlistItem && playlistItem.data && playlistItem.extended_data) {
              let playlist = JSON.parse(JSON.stringify(playlistItem));
              if (playlist.extended_data.playback_type !== "around_the_clock") {
                let start = null;
                let end = null;
                let startMinutes = null;
                let endMinutes = null;
                if (playlist.extended_data.playback_type === "default") {
                  start = playlist.time_start || playlist.data.time_start;
                } else {
                  if (playlist.extended_data.playback_type === "repeating" && playlist.extended_data.playback_days[dayIndex]) { //
                    startMinutes = this.getMinutesFromText(playlist.extended_data.playback_time_start);
                    if (startMinutes === false) {
                      console.log(playlist);
                    }
                    start = dayStart + startMinutes * 60;
                  }
                }
                if (start) {
                  if (playlist.extended_data.type === "default") {
                    end = this.getPlaylistEnd(playlist);
                    if (playlist.extended_data.playback_type === "repeating") {
                      end = dayStart + startMinutes * 60 + end;
                    }
                  } else {
                    if (playlist.extended_data.playback_limit.type === "by_time") {
                      endMinutes = this.getMinutesFromText(playlist.extended_data.playback_limit.value);
                    } else {
                      if (playlist.extended_data.playback_limit.type === "by_number") {
                        if (!playlist._randomLength) {
                          playlist._randomLength = this.getRandomLength(playlist);
                        }
                        if (playlist.extended_data.playback_type === "default") {
                          end = start + playlist._randomLength;
                        } else {
                          endMinutes = startMinutes + Math.floor(playlist._randomLength / 60);
                        }
                      } else {
                        if (playlist.extended_data.playback_limit.type === "by_minutes") {
                          if (playlist.extended_data.playback_type === "default") {
                            end = start + Math.floor(parseInt(playlist.extended_data.playback_limit.value) / 60);
                          } else {
                            endMinutes = startMinutes + parseInt(playlist.extended_data.playback_limit.value);
                          }
                        } else {
                          if (playlist.extended_data.playback_limit.type === "by_date") {
                            end = parseInt(playlist.extended_data.playback_limit.value);
                          }
                        }
                      }
                    }
                    if (endMinutes) {
                      end = dayStart + endMinutes * 60;
                    }
                  }

                  if (start && end) {
                    if (start <= dayEnd && end >= dayStart && start >= dayStart) {
                      let top = 0;
                      let startSeconds = 0;
                      if (start < dayEnd && start > dayStart) {
                        startSeconds = start - dayStart;
                        top = ((startSeconds) / seconds * 100);
                      }
                      let endSeconds = seconds;
                      let width = 100 - top;

                      if (end < dayEnd && end > dayStart) {
                        endSeconds = (end - dayStart);
                        width = ((endSeconds) / seconds * 100) - top;
                      } else {
                        let additionalEndSeconds = end - dayStart - seconds;
                        let additionalWidth = additionalEndSeconds / seconds * 100;
                        let additionalPlaylist = JSON.parse(JSON.stringify(playlist));
                        additionalPlaylist._style = {
                          top: '0',
                          height: `calc(${additionalWidth}% - .25em)`,
                        };
                        if (playlist.extended_data.color) {
                          additionalPlaylist._style.backgroundColor = playlist.extended_data.color;
                        }
                        if (playlistsForDays[index + 1]) {
                          additionalPlaylist._times = {
                            start: 0,
                            end: additionalEndSeconds
                          };
                          playlistsForDays[index + 1].push(additionalPlaylist);
                        }
                      }
                      playlist._formattedDates = {
                        start: getTime(dayStart + startSeconds),
                        end: getTime(dayStart + endSeconds)
                      };

                      playlist._times = {
                        start: startSeconds,
                        end: endSeconds
                      };
                      playlist._style = {
                        top: top + '%',
                        height: `calc(${width}% - .25em)`,
                      };
                      //console.log(playlist.extended_data);
                      if (playlist.extended_data.color) {
                        playlist._style.backgroundColor = playlist.extended_data.color;
                      }
                      playlistsForDays[index].push(playlist);
                    }
                  }
                }
              }
            }
          });
        });
        if (aroundTheClockPlaylist) {
          Object.keys(playlistsForDays).forEach(key => {
            playlistsForDays[key] = playlistsForDays[key].sort((a, b) => {
              if (a._times.start < b._times.start) {
                return -1;
              }
              if (a._times.start > b._times.start) {
                return 1;
              }
              return 0;
            })
            let aroundTheClockIntervals = [];
            if (playlistsForDays[key].length > 0) {
              if (playlistsForDays[key][0]._times.start !== 0) {
                aroundTheClockIntervals.push({
                  start: 0,
                  end: playlistsForDays[key][0]._times.start
                });
              }
              for (let i = 0; i < playlistsForDays[key].length - 1; i++) {
                if (playlistsForDays[key][i]._times.end !== playlistsForDays[key][i + 1]._times.start) {
                  aroundTheClockIntervals.push({
                    start: playlistsForDays[key][i]._times.end,
                    end: playlistsForDays[key][i + 1]._times.start
                  })
                }
              }
              if (playlistsForDays[key][playlistsForDays[key].length - 1]._times.end !== seconds) {
                aroundTheClockIntervals.push({
                  start: playlistsForDays[key][playlistsForDays[key].length - 1]._times.end,
                  end: seconds
                });
              }
            } else {
              aroundTheClockIntervals.push({start: 0, end: seconds});
            }
            aroundTheClockIntervals.forEach(interval => {
              let playlist = JSON.parse(JSON.stringify(aroundTheClockPlaylist));
              playlist._times = interval;
              let height = ((interval.end - interval.start) / seconds) * 100;
              playlist._formattedDates = {
                start: getTime((this.days[key].ts + interval.start)),
                end: getTime((this.days[key].ts + interval.end))
              };
              playlist._style = {
                background: '#ccc',
                top: interval.start / seconds * 100 + '%',
                height: `calc(${height}% - .25em)`,
              };
              playlistsForDays[key].push(playlist);
            })
          });
        }
        this.playlistsForDays = playlistsForDays;
      }
    },
    async mounted() {
      if (!this.error) {
        this.setDays();
        this.onResize();
        this.setItemsTime();
        this.projects = (await this.$axios.post(`/projects/getbychannel/${this.channel.id}`)).data.list.data;
        if (this.isChannelAdmin) {
          let team = (await this.$axios.post(`/channels/${this.channel.id}/team/get`, {
            types: ['channel_admin', 'admin', 'owner', 'broadcaster']
          })).data.list;
          let users = team.filter(item => item.user_id !== this.$store.state.userData.id).map(item => {
            return {
              name: item.username,
              value: item.user_id
            }
          });
          users.unshift({
            name: `${this.$t('scheduler.you')} (${this.$store.state.userData.username})`,
            value: this.$store.state.userData.id
          });
          this.broadcastingUsers = users;
          this.setNextItemsInterval();
        }
      }
    },
    data() {
      return {
        nextItemsInterval: null,
        nextItems: [],
        currentTab: 'playlists',
        deletePlaylistPanel: {
          visible: false,
          loading: false,
          data: null
        },
        playlistsToSave: {
          items: [],
          state: null,
          percent: 0
        },
        playlistItemEditPanel: {
          editIndex: -1,
          playlistEditIndex: -1,
          folderIndex: -1,
          visible: false,
          errors: {},
          data: {
            title: '',
            description: '',
            tags: []
          }
        },
        isResizing: false,
        timetableView: localStorage.scheduler_timetable_view === "true",
        projects: [],
        playlistsForDays: {

        },
        playlistPanel: {
          visible: false
        },
        currentWeekIntervalText: '',
        timetableTop: 0,
        timetableHeight: 100,
        hoursListClass: '',
        showItemDates: false,
        currentPlaylist: null,
        days: [],
        broadcastingUsers: [],
        announcePanel: {
          response: null,
          visible: false,
          loading: false,
          isEditing: false,
          data: defaultAnnounceData,
          coverData: null,
          errors: {

          }
        },
        currentWeekStart: startOfWeek(new Date(), {
          weekStartsOn: 1
        })
      }
    },
    async asyncData({app, params, redirect}) {
      let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
      if (channelData.status) {
        if (channelData.data.is_radio) {
          return redirect(`/radio-scheduler/${params.id}`);
        }
        let channel = channelData.data;
        let id = params.id;
        let permissions = (await app.$axios.post(`/channels/${params.id}/getpermissions`)).data;
        if (permissions.length > 0) {
          let schedule = (await app.$api.get(`/timetable/getbychannel/${params.id}/all`));
          if (schedule.status) {
            return {
              error: false,
              schedule: schedule.data,
              channel,
              id,
              permissions
            };
          } else {
            return {
              error: schedule
            };
          }
        } else {
          return {
            error: {
              text: 'errors.403'
            }
          };
        }
      } else {
        return {
          error:
            {
              text: 'errors.404'
            }
        };
      }
    },
    components: {
      AnnouncesManager,
      announcePanel,
      VideoMetadataEditor,
      playlistEditor,
      MediaManager,
      ResizableRow,
      ResizableRowChild,
      ResizableRowBar,
    }
  }
</script>
<style lang="scss">
  .scheduler {
    &__container {
      height: 100%;
    }
    &__outer {
      height: 100%;
    }
    &__inner {
      height: 100%;
    }
    &__hours {
      user-select: none;
      margin: 2em 0 0;
      height: 100%;
      &__list {
        position: relative;
        display: flex;
        flex-direction: column;
        height: calc(100% - 2em);
        justify-content: space-around;
      }
    }

    &__hour {
      font-size: .75em;
      padding: .5em 2em;
      flex: 1;
      text-align: center;
      position: relative;
      &:after {
        content: "";
        display: block;
        height: 1px;
        width: 100%;
        left: 0;
        background: rgba(255, 255, 255, 0.1);
        position: absolute;
        top: 0;
      }
    }
    &__hours__list--small &__hour:nth-of-type(2n) {
      display: none;
    }
    &__days {
      overflow: hidden;
      display: flex;
      flex-direction: row;
      flex: 1;
    }
    &__day {
      display: flex;
      flex-direction: column;
      flex: 1;
      &__text {
        text-align: center;
        padding: .5em 1em;
        font-size: .875em;
        background: var(--box-color);
      }
      &__playlists {
        flex: 1;
        position: relative;
      }
    }

    &__playlist {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      cursor: pointer;
      position: absolute;
      margin: .25em;
      width: calc(100% - .25em);
      box-sizing: border-box;
      line-height: .85;
      background: var(--active-color);
      min-height: 1em;
      &__dates {
        padding: .5em;
        font-size: .65em;
        text-align: center;
        background: #ffffff4a;
        color: #000;
        font-weight: 500;
      }
      &--announce {
        background: var(--green);
      }
      &:hover {
        box-shadow: 0 0 1em rgba(255, 255, 255, .5);
      }
    }
    &__timetable-container {
      display: flex;
      height: calc(100% - 3em);
      &__outer {
        overflow: hidden;
        height: 100%;
        flex: 1;
        max-height: calc(100% - 3em);
        &--with-next-items {
          max-height: calc(100% - 6.5em);
        }
      }
    }

    &__bottom-panel {
      &-outer {
        height: 100%;
        overflow: hidden;
      }
      &-container {
        padding: .5em .5em 2.25em!important;
        height: calc(100% - 5.25em);
        overflow: hidden;
        .bottom-panel__inner {
          margin: -1em -.5em;
        }
        .bottom-panel__item {
          padding: .25em;
          &__title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
          &__top {
            height: 5em;
          }
          &__outer {
            width: calc(100% / 5);
          }
        }
      }
      &__header-inner {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      &__dates {
        display: flex;
        align-items: center;
        &__week {
          white-space: nowrap;
          font-size: .875em;
          background: rgba(0, 0, 0, .25);
          padding: .5em 1em;
          border-radius: .35em;
          margin: 0 .5em;
          font-weight: 500;
        }
      }
    }



    &__playlist-contents {
      .select {
        margin: 1.5em 0;
      }
      &__footer {
        &__buttons {
          display: flex;
          align-items: center;
          justify-content: space-between;
        }
      }
      &__items {
        flex: 1;
        overflow: auto;
        margin: .25em 0 0;
      }
      &__types {
        margin: 1em 0 1.25em;
        font-size: 1.25em;
      }
      &__header {

      }
      &__items-button {
        border: 1px solid var(--input-border-color);
        padding: 1em;
        margin: 1em 0;
        cursor: pointer;
        font-size: 1.25em;
        &:hover {
          background: rgba(255, 255, 255, 0.05);
        }
      }
      &__item {
        &__folder {
          background: var(--title-box-color);
          padding: .5em;
        }
        &__inner {
          padding: .5em;
          background: var(--box-color);
          margin: .25em;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: space-between;
        }
        &__title {
          margin: 0 .5em 0 0;
          max-width: 75%;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        &__time {
          font-weight: 500;
          font-size: .875em;
          margin: 0 .5em 0 0;
          line-height: 0;
        }
        &__left {
          max-width: calc(100% - 5.5em);
          flex: 1;
          display: flex;
          align-items: center;
        }
        &__right {
          display: flex;
          align-items: center;
        }

        &__button {
          margin: 0 0 0 .5em;
          cursor: pointer;
          opacity: .75;
          transition: all .4s;
          &:hover {
            opacity: 1;
          }
        }
      }
      &__footer {

      }
    }
     &__announce-panel {

     }

    &__classic-view {
      height: calc(100% - 3em);
      &--with-next-items {
        height: calc(100% - 6.5em);
      }
      &__announces {
        flex: 1;
      }

      &__media-manager {
        margin: 1em;
        height: calc(100% - 2em);
        .media-manager__inner {
          height: calc(100% + 2.25em);
        }

        .video-add {
          display: none;
        }
      }
      &__playlists {
        height: 100%;
        display: flex;
        flex-direction: column;
        &__inner {
          flex: 1;
          overflow: auto;
        }

        &__bottom-panel {
          padding: 1em .5em;
          background: var(--box-element-color);
          height: 1.75em;
          margin: 0 0 0 -.5em;
          &__text {
            font-size: .875em;
            margin: 0 1em;
          }
        }
      }
      .resizable-row {
        height: 100%;
      }

      &__playlist {
        margin: .5em .5em 0 0;
        &__header {
          padding: .5em;
          font-weight: 500;
          text-align: center;
          color: var(--box-footer-color);
          display: flex;
          align-items: center;
          justify-content: space-between;
          &__button {
            cursor: pointer;
            color: var(--box-footer-color);
          }
          &--dark {
            color: var(--text-color);
          }
          &--dark &__button {
            color: var(--text-color);
          }
        }
        &__inner {
          min-height: 5em;
          border: 2px solid;
          overflow: hidden;
        }
        &__item {
          border-bottom: 1px solid rgba(255, 255, 255, .25);
          &__inner {
            padding: .5em;
            display: flex;
            align-items: center;
            justify-content: flex-start;
          }
          &__time {
            font-size: .875em;
            margin: 0 .5em;
            font-weight: 600;
          }

          &__title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0 0 0 .5em;
            flex: 1;
          }

          &__button {
            cursor: pointer;
            opacity: .75;
            &:hover {
              opacity: 1;
            }
          }
          &__folder-contents {
            padding: 0 0 0 1em;
            border-top: 1px solid rgba(255, 255, 255, .2);
          }
        }
      }
    }

    &__next-items {
      display: flex;
      align-items: center;
      background: var(--title-box-color);
      overflow: hidden;
      height: 3.5em;
      &__list {
        display: flex;
        flex: 1;
      }
      &__title {
        padding: 0 1.5em;
        white-space: nowrap;
        font-weight: 400;
      }

    }

    &__next-item {
      flex: 1;
      max-width: 33%;
      margin: 0 .25em 0 0;
      background: var(--box-element-color);
      &:last-of-type {
        margin: 0;
        max-width: calc(33% + .5em);
      }
      &__title {
        font-size: .875em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: .5em;
      }
      &__time {
        padding: .5em .5em .25em;
        background: var(--active-color);
        font-weight: bold;
      }
    }

  }
</style>
