<template>
  <div class="studio__container">
    <div class="studio__outer" v-if="!error">
      <div class="studio">
        <div class="studio__main">
          <div class="studio__controls">

            <div class="studio__overlays">

              <c-modal
                :header="overlayPanel.isEditing ? $t('studio.library.edit_overlay') : $t('studio.library.add_new_overlay')"
                v-model="overlayPanel.visible">
                <div slot="main">
                  <div class="overlay-panel">
                    <div class="overlay-panel__preview" :style="{width: settings.sizeX+'px', height: settings.sizeY+'px'}">
                      <component ref="preview_component" v-if="overlayPanel.overlay.component" :is="overlayPanel.overlay.component" v-model="overlayPanel.overlay.object.data" :object="overlayPanel.overlay.object"/>
                    </div>
                    <div class="overlay-panel__inputs"> <!-- :style="{height: settings.sizeY+'px'}" -->
                      <div v-if="getOverlayDescription" class="overlay-panel__description">
                        {{getOverlayDescription}}
                      </div>
                      <c-input :title="$t('studio.library.title')" v-model="overlayPanel.overlay.title"/>
                      <div class="vertical-delimiter overlay-panel__vertical-delimiter"></div>
                      <div class="overlay-panel__inputs__section" :key="$index" v-for="(section, $index) in getInputsForOverlay(overlayPanel.overlay.object)">
                        <div class="overlay-panel__inputs__section__name">{{$t(section.name)}}</div>
                        <div class="overlay-panel__inputs__section__contents">
                          <div class="row row--centered row--vertical-mobile" :key="$index2" v-for="(row, $index2) in section.inputs">
                            <div class="col studio__overlay-input-container" :class="{'col--auto': (input.type === 'color' && ($index3 === 0 || row.length - 1 === $index3))}" :key="$index3" v-for="(input, $index3) in row">
                              <c-input :min="getInputProperty(input, 'min')" :max="getInputProperty(input, 'max')" :disabled="getDisabledState(input, overlayPanel.overlay.object.data)" :slider="input.slider" :percent="input.percent" :title="$t(input.name)" v-model="overlayPanel.overlay.object.data[input.id]" v-if="input.type === 'text' || input.type === 'input'" :type="input.inputType"/>
                              <c-colorpicker v-else-if="input.type === 'color'" :titleLeft="$t(input.name)" v-model="overlayPanel.overlay.object.data[input.id]"/>
                              <c-checkbox v-else-if="input.type === 'checkbox'" :title="$t(input.name)" v-model="overlayPanel.overlay.object.data[input.id]"/>
                              <c-picture-uploader :returnData="true" :big="true" folder="overlays" v-else-if="input.type === 'picture'" :title="$t(input.name)" v-model="overlayPanel.overlay.object.data[input.id]"/>
                              <c-select :options="input.values" v-else-if="input.type === 'select'" :title="$t(input.name)" v-model="overlayPanel.overlay.object.data[input.id]"/>
                              <component :channel="channel" :input="input" :is="input.component" v-else-if="input.type === 'custom'" v-model="overlayPanel.overlay.object.data[input.id]"/>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button color="green" :loading="overlayPanel.saving" v-if="!overlayPanel.isEditing" @click="addOverlayToScene()">{{ $t('studio.add_overlay_to_scene')}}
                    </c-button>
                    <c-button v-if="overlayPanel.isEditing" :loading="overlayPanel.saving" @click="saveOverlay()">
                      {{!overlayPanel.isEditing ? $t('studio.add_overlay_to_library') : $t('global.save')}}
                    </c-button>
                    <c-checkbox v-if="!overlayPanel.isEditing" v-model="overlayPanel.addToLibrary" :title="$t('studio.add_overlay_to_library')"/>
                  </div>
                </div>
              </c-modal>


              <c-modal :header="$t('studio.library.delete_overlay')" v-model="deleteOverlayPanel.visible">
                <div slot="main">
                  <div class="modal__text">{{$t('studio.library.confirm_deletion')}}</div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="deleteOverlay()" :loading="deleteOverlayPanel.loading">{{$t('global.ok')}}</c-button>
                    <c-button flat @click="deleteOverlayPanel.visible = false">{{$t('global.cancel')}}</c-button>
                  </div>
                </div>
              </c-modal>

              <c-modal :header="$t('studio.resume.heading')" v-model="needResume">
                <div slot="main">
                  <div class="modal__text">{{$t('studio.resume.text')}}</div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="resumeContexts()">{{$t('global.ok')}}</c-button>
                  </div>
                </div>
              </c-modal>


              <c-modal :header="$t('studio.settings.heading')" v-model="settingsPanel.visible">
                <div slot="main" v-if="settingsPanel.data">
                  <div class="row">
                    <div class="col">
                      <div class="modal__input-container">
                        <c-input :min="320" :max="1280" :title="$t('studio.settings.size_x')"
                                 v-model="settingsPanel.data.sizeX" type="number"/>
                      </div>
                    </div>
                    <div class="col">
                      <div class="modal__input-container">
                        <c-input :min="180" :max="720" :title="$t('studio.settings.size_y')"
                                 v-model="settingsPanel.data.sizeY" type="number"/>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <c-button @click="saveSettings()">{{$t('global.save')}}</c-button>
                </div>
              </c-modal>

              <c-modal v-model="deleteScenePanel.visible">
                <div slot="main">
                  <div class="modal__text">
                    {{$t('studio.delete_scene_text')}}
                  </div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="deleteSelectedScene()">{{$t('global.ok')}}</c-button>
                    <c-button flat @click="deleteScenePanel.visible = false">{{$t('global.cancel')}}</c-button>
                  </div>
                </div>
              </c-modal>

              <c-modal :header="$t('studio.add_scene')" v-model="addScenePanel.visible">
                <div slot="main">
                  <div class="modal__input-container">
                    <c-input :title="$t('studio.scene_name')" v-model="addScenePanel.data.name"/>
                  </div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="addScene()">{{$t('global.ok')}}</c-button>
                    <c-button flat @click="addScenePanel.visible = false">{{$t('global.cancel')}}</c-button>
                  </div>
                </div>
              </c-modal>

              <c-modal :header="$t('studio.add_scenes_list')" v-model="addScenesListPanel.visible">
                <div slot="main">
                  <div class="modal__input-container">
                    <c-input :title="$t('studio.scenes_list_name')" v-model="addScenesListPanel.data.name"/>
                  </div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="addScenesList()" :loading="addScenesListPanel.loading">{{$t('global.ok')}}</c-button>
                    <c-button flat @click="addScenePanel.visible = false">{{$t('global.cancel')}}</c-button>
                  </div>
                </div>
              </c-modal>

              <c-modal v-model="scenesListsPanel.visible">
                <div slot="main">
                  <div class="buttons-row">
                    <c-button @click="addNewScenesList()">{{$t('studio.add_scenes_list')}}</c-button>
                  </div>
                  <div class="vertical-delimiter"></div>
                  <div class="list-item list-item--not-link list-item--small"
                       :class="{'list-item--selected': item.id === currentScenesListID}" :key="$index"
                       v-for="(item, $index) in scenesLists">
                    <div class="list-item__left">
                      <div class="list-item__captions">
                        <div class="list-item__title">{{item.name}}</div>
                        <div class="list-item__under-title">{{formatPublishDate(item.created_at, false)}}</div>
                      </div>
                    </div>
                    <div class="list-item__right">
                      <div class="list-item__buttons">
                        <div class="buttons-row">
                          <c-button @click="loadScenesList(item)">{{$t('studio.load_scenes_list')}}</c-button>
                          <c-button color="red" @click="deleteScenesList(item)">{{$t('global.delete')}}</c-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </c-modal>

              <c-modal v-model="deleteScenesListPanel.visible">
                <div slot="main">
                  <div class="modal__text">{{$t('studio.confirm_scenes_list_deletion')}}</div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="deleteSelectedScenesList()" :loading="deleteScenesListPanel.loading">
                      {{$t('global.ok')}}
                    </c-button>
                    <c-button flat @click="deleteScenesListPanel.visible = false">{{$t('global.cancel')}}</c-button>
                  </div>
                </div>
              </c-modal>

              <div class="studio__overlays__header">
                <span class="studio__overlays__header__text">
                  {{$t('studio.overlays.heading')}}
                </span>
              </div>

              <draggable v-model="overlayIndexes[currentScene]" :options="{handle: '.box--scene-overlay__drag'}" class="studio__overlays__inner">
                <div :data-index="$index" v-if="scenes[currentScene].overlays[$index]"
                     class="box box--with-header box--scene-overlay"
                     :class="{'box--disabled': scenes[currentScene].overlays[$index].disabled}" :key="$index"
                     v-for="$index in overlayIndexes[currentScene]">
                  <div class="box__header">
                    <span class="box__header__title">{{getOverlayTitle(scenes[currentScene].overlays[$index])}}</span>
                    <div class="box__header__buttons">
                      <a class="box__header__button box--scene-overlay__drag">
                        <i class="fa fa-arrows-alt"></i>
                      </a>
                      <a class="box__header__button" @click="editSceneOverlay(scenes[currentScene].overlays[$index])">
                        <i class="fa fa-chevron-up" v-if="scenes[currentScene].overlays[$index].settingsVisible"></i>
                        <i class="fa fa-chevron-down" v-else></i>
                      </a>
                      <a class="box__header__button"
                         :class="{'box__header__button--active': scenes[currentScene].overlays[$index].disabled}"
                         @click="toggleOverlay(scenes[currentScene].overlays[$index])">
                        <i class="fa fa-power-off"></i>
                      </a>
                      <a class="box__header__button"
                         @click="deleteOverlayFromList(scenes[currentScene].overlays[$index], $index)">
                        <i class="fa fa-times"></i>
                      </a>
                    </div>
                    <!--
                    <span class="box__header__icon-container" @click="showHideOverlay($index)">
                      <i class="material-icons" v-if="!scenes[currentScene].overlays[$index].hidden">arrow_drop_up</i>
                      <i class="material-icons" v-else>arrow_drop_down</i>
                    </span>
                    -->
                  </div>
                  <div class="box__inner" v-show="scenes[currentScene].overlays[$index].settingsVisible || true" v-if="!scenes[currentScene].overlays[$index]._hideInputs">
                     <div class="box--scene-overlay__section" :key="$index4" v-for="(section, $index4) in getInputsForOverlay(scenes[currentScene].overlays[$index].object)">
                      <div v-show="!section.alwaysVisible" class="box--scene-overlay__section__header">
                        <div class="box--scene-overlay__section__name" v-if="section.name">{{$t(section.name)}}</div>
                        <div class="box--scene-overlay__section__buttons">
                          <a @click="toggleSection(scenes[currentScene].overlays[$index], section)"
                             class="box--scene-overlay__section__button">
                            <i class="fa fa-plus" v-if="scenes[currentScene].overlays[$index].closedSections && scenes[currentScene].overlays[$index].closedSections[section.name]"></i>
                            <i class="fa fa-minus" v-else></i>
                          </a>
                        </div>
                      </div>
                      <div class="box--scene-overlay__section__contents"
                           v-show="!scenes[currentScene].overlays[$index].closedSections || !scenes[currentScene].overlays[$index].closedSections[section.name] || section.alwaysVisible">
                        <div class="row row--centered row--vertical-mobile" :key="$index2"
                             v-for="(row, $index2) in section.inputs">
                          <div class="col studio__overlay-input-container"
                               :class="{'col--auto': (input.type === 'color' &&  ($index3 === 0 || row.length - 1 === $index3))}"
                               :key="$index3" v-for="(input, $index3) in row">

                            <c-input
                                     :min="getInputProperty(input, 'min', scenes[currentScene].overlays[$index].object.data)"
                                     :max="getInputProperty(input, 'max', scenes[currentScene].overlays[$index].object.data)"
                                     :disabled="getDisabledState(input, scenes[currentScene].overlays[$index].object.data)"
                                     :slider="input.slider"  :percent="input.percent" :title="$t(input.name)"
                                     v-model="scenes[currentScene].overlays[$index].object.data[input.id]"
                                     v-if="input.type === 'input'" :type="input.inputType"/>
                            <c-colorpicker v-else-if="input.type === 'color'" :titleLeft="$t(input.name)"
                                           v-model="scenes[currentScene].overlays[$index].object.data[input.id]"/>
                            <c-checkbox v-else-if="input.type === 'checkbox'" :title="$t(input.name)"
                                        v-model="scenes[currentScene].overlays[$index].object.data[input.id]"/>
                            <c-picture-uploader :returnData="true" :big="true" folder="overlays"
                                          v-else-if="input.type === 'picture'" :title="$t(input.name)"
                                          v-model="scenes[currentScene].overlays[$index].object.data[input.id]"/>


                            <c-select :options="input.values" v-else-if="input.type === 'select'" :title="$t(input.name)" v-model="scenes[currentScene].overlays[$index].object.data[input.id]"/>

                            <component :overlay="scenes[currentScene].overlays[$index]" :channel="channel"  :input="input" :is="input.component" v-else-if="input.type === 'custom'" v-model="scenes[currentScene].overlays[$index].object.data[input.id]"/>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </draggable>
              <div class="studio__overlays__bottom">
                <div class="studio__scenes__list">
                  <div class="studio__scene" :key="$index" :class="{'studio__scene--active': currentScene === $index}" v-for="(scene, $index) in scenes">
                    <span class="studio__scene__name" @click="setCurrentScene($index)">{{scene.name}}</span>
                    <span @click="deleteScene(scene, $index)" v-if="scenes.length > 1" class="studio__scene__icon studio__scene__icon--delete">
                    <i class="fa fa-times"></i>
                  </span>
                  </div>
                  <div class="studio__scene studio__scene--add" @click="showAddScenePanel()">
                  <span class="studio__scene__icon studio__scene__icon--add">
                    <i class="fa fa-plus"></i>
                  </span>
                  </div>
                </div>
                <div class="studio__overlays__bottom__inner">
                  <c-button color="green" @click="addOverlayMenuVisible = !addOverlayMenuVisible">
                    {{$t('global.add')}}
                  </c-button>
                  <c-popup-menu class="popup-menu-top popup-menu--big" :manual="true" :visible="addOverlayMenuVisible">
                    <c-popup-menu-item>{{$t('studio._tabs.overlays')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in getOverlaysList" @click="openOverlayAddPanel(item)">
                          <span class="popup-menu__icon" v-html="getOverlayIcon(item)"></span>
                          {{item.title}}
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>

                    <c-popup-menu-item v-if="library && library.list && library.list.length > 0">{{$t('studio._tabs.library')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in library.list">
                          <div @click="addOverlayFromLibrary(item)" class="popup-menu__texts">
                            <span class="popup-menu__icon" v-html="getOverlayIcon(item)"></span>
                            {{item.title}}
                          </div>
                          <div class="popup-menu__buttons">
                            <a @click="openOverlayEditPanel(item)" class="popup-menu__button"><i class="material-icons">edit</i></a>
                            <a @click="openOverlayDeletePanel(item)" class="popup-menu__button"><i class="material-icons">clear</i></a>
                          </div>
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>
                    <!--
                    <c-popup-menu-item >{{$t('studio._tabs.library')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in library.list">
                          <div @click="openOverlayAddPanel(item)" class="popup-menu__texts">
                            <span class="popup-menu__icon" v-html="getOverlayIcon(item)"></span>
                            {{item.title}}
                          </div>
                          <div class="popup-menu__buttons">
                            <a @click="openOverlayEditPanel(item)" class="popup-menu__button" ><i class="material-icons">edit</i></a>
                            <a @click="openOverlayDeletePanel(item)" class="popup-menu__button"><i class="material-icons">clear</i></a>
                          </div>
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>
                    -->
                  </c-popup-menu>
                </div>
              </div>
            </div>
          </div>
          <div class="studio__right" :style="{'width': settings.sizeX + 'px'}">
            <div class="studio__preview">
              <div class="studio__preview__video">
                <div ref="main_field" class="studio__main-field" :style="{height: settings.sizeY + 'px'}">
                  <div class="studio__scene-instance" :class="{'studio__scene-instance--visible': currentScene === $index}" v-for="(scene, $index) in scenes" :key="$index">
                    <div class="studio__overlay"  v-for="(overlay, $overlay_index) in scene.overlays" :key="$overlay_index">
                      <component :audio="audio" :zIndex="overlayIndexes[currentScene] ? overlayIndexes[currentScene][$overlay_index] + 1 : 0" :disabled="overlay.disabled" v-if="overlay.object" :ref="'overlays_' + $index + '_'+$overlay_index" :is="overlay.component" v-model="overlay.object.data" :object="overlay.object"/>
                    </div>
                  </div>
                </div>
              </div>

              <div class="studio__preview__buttons">
                <div class="buttons-list">
                  <c-button :loading="broadcast.streamState === 'STARTING' || broadcast.streamState === 'STOPPING'"
                         :class="{'button--green': (broadcast.streamState !== 'STARTED'), 'button--red': (broadcast.streamState === 'STARTED' || broadcast.streamState === 'STOPPING')}"
                         @click="startStopStream()" icon="fa-video">{{(broadcast.streamState === 'STARTED' ||
                    broadcast.streamState === 'STOPPING') ? $t('studio.stop_broadcast') : $t('studio.start_broadcast')}}
                  </c-button>
                  <c-button :disabled="broadcast.streamState !== 'NOT_STARTED'" @click="openSettingsPanel()" icon="fa-cog">
                    {{$t('studio.settings.heading')}}
                  </c-button>
                </div>
              </div>
              <!--
              <div class="studio__chat">
                <Chat ref="chat" :channel="channel"/>
              </div>
            -->
            </div>
            <div class="studio__scenes">
              <div class="studio__scenes__buttons">
                <div class="buttons-list">
                  <c-button :loading="savingScenesLists" flat @click="saveScenes()">{{$t('studio.save_scenes')}}</c-button>
                  <c-button @click="scenesListsPanel.visible = true">{{$t('studio.load_scenes')}}</c-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <c-error-page v-else :data="error"/>
  </div>
</template>

<script>

    import clickOutside from 'vue-click-outside';
    import StreamManager from "@/helpers/studio/streammanager";

    import draggable from 'vuedraggable';

    import {formatPublishDate} from '@/helpers/dates.js';

    import Chat from '@/components/Chat';

    import sourcesList from '@/helpers/studio/sources_v2.js';

    import StudioPicturePicker from "@/components/new-studio/StudioPicturePicker";
    import StudioConferenceManager from "@/components/new-studio/StudioConferenceManager";

    import CameraSource from "@/components/new-studio/CameraSource";
    import IframeSource from "@/components/new-studio/IframeSource";
    import TextSource from "@/components/new-studio/TextSource";
    import PictureSource from "@/components/new-studio/PictureSource";
    import ConferenceSource from "@/components/new-studio/ConferenceSource";

    const getInputProperty = (item, fn, data) => {
        if (item[fn]) {
            if (typeof item[fn] === 'function') {
                return item[fn]({
                    data: data,
                    settings: this.settings
                });
            }
            return item[fn];
        }
        return null;
    }

    export default {
        middleware: 'auth',
        directives: {
            clickOutside
        },
        watch: {
            settings(newSettings) {
                localStorage.broadcast_studio_settings = JSON.stringify(newSettings);
            },
            "overlayPanel.visible"(isVisible) {
                if (isVisible && !this.overlayPanel.isEditing) {
                    //this.setDefaultInputValues();
                } else {
                    if (!isVisible) {
                        this.overlayPanel.isEditing = false;
                    }
                }
            },
            "overlayPanel.overlay.typeName"(typeName, oldTypeName) {
                if (oldTypeName.length > 0 && !this.overlayPanel.isEditing) {
                    //this.setDefaultInputValues();
                }
            },
        },
        computed: {
            tabs() {
                return [
                    {id: 'library', name: this.$t('studio._tabs.library')},
                    {id: 'overlays', name: this.$t('studio._tabs.overlays')},
                    {id: 'sources', name: this.$t('studio._tabs.sources')},
                ];
            },
            getInputsForOverlayPanel() {
                return this.getInputsForOverlay(this.overlayPanel.overlay);
            },
            getOverlayDescription() {
                let typeName = this.overlayPanel.overlay.typeName;
                if (sourcesList[typeName]) {
                    return this.$t(sourcesList[typeName].description);
                }
                return null;
            },
            getOverlaysList() {
                let list = [];
                Object.keys(sourcesList).forEach(key => {
                    list.push({
                        title: this.$t(sourcesList[key].title),
                        typeName: sourcesList[key].typeName
                    })
                });
                return list;
            },
        },
        methods: {
            initAudioCtx() {
                this.audio.ctx = new AudioContext();
                this.audio.merger =  this.audio.ctx.createChannelMerger(20);
                let destination = this.audio.ctx.createMediaStreamDestination();
                this.audio.destination = destination;
                this.audio.merger.connect(destination);
                let audio = document.createElement('audio');
                audio.controls = "controls";
                audio.style.position = "fixed";
                audio.style.zIndex = "1000000000";
                document.body.appendChild(audio);
                audio.srcObject = destination.stream;
            },
            async addOverlayToScene() {
                if (this.overlayPanel.addToLibrary) {
                    await this.saveOverlay();
                }
                this.addOverlay(this.overlayPanel.overlay);
                this.overlayPanel.visible = false;
            },
            addOverlayFromLibrary(item) {
                let object = new sourcesList[item.type_name];
                object.data = JSON.parse(JSON.stringify(item.data));
                let overlay = {
                    title: item.title,
                    typeName: item.type_name,
                    component: object.componentName,
                    object
                }
                if (!this.overlayIndexes[this.currentScene]) {
                    this.overlayIndexes[this.currentScene] = [];
                }
                this.overlayIndexes[this.currentScene].push(this.scenes[this.currentScene].overlays.length);
                this.scenes[this.currentScene].overlays.push(overlay);
                let refName = 'overlays_' + (""+this.currentScene) + '_' + (""+(this.scenes[this.currentScene].overlays.length - 1));
                this.$nextTick(() => {
                    object.componentInstance = this.$refs[refName][0];
                })
            },
            addOverlay(data) {
                let objectData = data.object.data;
                let object = new sourcesList[data.typeName];
                object.data = JSON.parse(JSON.stringify(objectData));
                let overlay = {
                    title: data.title,
                    typeName: data.typeName,
                    component: object.componentName,
                    object
                }
                if (!this.overlayIndexes[this.currentScene]) {
                    this.overlayIndexes[this.currentScene] = [];
                }
                this.overlayIndexes[this.currentScene].push(this.scenes[this.currentScene].overlays.length);
                this.scenes[this.currentScene].overlays.push(overlay);
                let refName = 'overlays_' + (""+this.currentScene) + '_' + (""+(this.scenes[this.currentScene].overlays.length - 1));
                this.$nextTick(() => {
                    object.componentInstance = this.$refs[refName][0];
                    if (this.window) {
                        let scene = this.window.document.getElementById('scene_' + this.currentScene);
                        if (scene) {
                            overlay.object.bindElement(scene);
                        }
                    }
                })
            },
            getInputProperty,
            resumeContexts() {
                this.loopCtx.resume();
                this.audioCtx.resume();
                this.needResume = false;
            },
            initStreamManager() {
                const key = this.keyData.key.stream_key;
                this.broadcast.streamManager = new StreamManager({
                    wsUrl: 's2.catcast.tv',
                    wsQuery: `name=${this.channel.stream_name}&key=${key}`
                });
            },
            async updateOverlays(scenes) {
                let newScenes = [];
                scenes.forEach((scene, sceneIndex) => {
                    scene.overlays = scene.overlays.filter(overlay => overlay);
                    let newScene = {
                        name: scene.name,
                        overlays: []
                    }
                    scene.overlays.forEach((overlay, overlayIndex) => {
                        if (sourcesList[overlay.typeName]) {
                            let object = new sourcesList[overlay.typeName]({
                                disabled: sceneIndex !== 0
                            });
                            object.data = JSON.parse(JSON.stringify(overlay.data));
                            let newOverlay = {
                                title: overlay.title,
                                typeName: overlay.typeName,
                                component: object.componentName,
                                object
                            }
                            newScene.overlays.push(newOverlay);
                            if (!this.overlayIndexes[sceneIndex]) {
                                this.$set(this.overlayIndexes, sceneIndex, []);
                            }
                            this.overlayIndexes[sceneIndex].push(overlayIndex);

                        }
                    });
                    newScenes.push(newScene);
                });
                this.setCurrentScene(0);
                this.scenes = newScenes;
                this.$nextTick(() => {
                    this.scenes.forEach((scene, sceneIndex) => {
                        scene.overlays.forEach((overlay, overlayIndex) => {
                            let refName = 'overlays_' + (""+sceneIndex) + '_' + (""+(overlayIndex));
                            this.$nextTick(() => {
                                //if (this.$refs[refName] && this.$refs[refName][0]) {
                                    overlay.object.componentInstance = this.$refs[refName][0];
                                //}
                            });
                        })
                    })
                })
            },
            async setCurrentScene(index) {
                console.log('Old scene: ' + this.currentScene + ', New scene:' + index);
                console.log('overlayIndexes', this.overlayIndexes);
                this.scenes[this.currentScene].overlays.forEach(overlay => {
                    overlay.disabled = true;
                    if (overlay.object) {
                        overlay.object.disable();
                    }
                })
                this.scenes[index].overlays.forEach(overlay => {
                    overlay.disabled = false;
                    overlay.object.enable();
                })
                if (this.window) {

                    this.window.document.getElementById('scene_' + this.currentScene).classList.remove('scene--visible');
                    this.window.document.getElementById('scene_' + index).classList.add('scene--visible');
                }
                this.currentScene = index;
            },
            deleteSelectedScenesList() {
                this.$axios.delete(`scenes/${this.deleteScenesListPanel.data.id}`).then(res => {
                    this.deleteScenesListPanel.loading = false;
                    this.$store.commit('NEW_ALERT', res.data);
                    this.deleteScenesListPanel.visible = false;
                    this.scenesListsPanel.visible = false;
                    if (res.data.status) {
                        this.scenesLists = this.scenesLists.filter(item => item.id !== this.deleteScenesListPanel.item.id);
                    }
                })
            },
            deleteScenesList(list) {
                this.deleteScenesListPanel.data = list;
                this.deleteScenesListPanel.visible = true;
            },
            addNewScenesList() {
                this.currentScenesListID = null;
                this.scenes = [
                    {
                        name: 'Scene 1',
                        overlays: []
                    },
                ];
                this.scenesListsPanel.visible = false;
            },
            async loadScenesList(item) {
                this.scenes = item.data;
                this.scenesListsPanel.visible = false;
            },
            formatPublishDate,
            addScenesList() {
                this.addScenesListPanel.loading = true;
                let data = this.addScenesListPanel.data;
                data.channel_id = this.channel.id;
                data.studio_version = 2;
                this.$axios.post(`scenes`, data).then(res => {
                    this.addScenesListPanel.loading = false;
                    if (res.data.status) {
                        this.currentScenesListID = res.data.data.scenes.id;
                        this.scenesLists.unshift(res.data.data.scenes);
                        this.addScenesListPanel.visible = false;
                    } else {
                        this.$store.commit('NEW_ALERT', res.data);
                    }
                })
            },
            addScene() {
                this.$set(this.overlayIndexes, this.scenes.length, []);
                this.scenes.push({
                    name: this.addScenePanel.data.name,
                    overlays: []
                });
                this.addScenePanel.visible = false;
                if (this.window) {
                    let main = this.window.document.getElementById('main');
                    let sceneDiv = this.window.document.createElement('div');
                    sceneDiv.className = "scene";
                    sceneDiv.id = 'scene_' + ("" +this.scenes.length - 1);
                    main.appendChild(sceneDiv);
                }
            },
            showAddScenePanel() {
                this.addScenePanel.data.name = `Scene ${(this.scenes.length + 1)}`;
                this.addScenePanel.visible = true;
            },
            deleteSelectedScene() {
                let index = this.deleteScenePanel.index;
                if (this.window) {
                    let sceneEl = this.window.document.getElementById('scene_' + index);
                    if (sceneEl) {
                        sceneEl.remove();
                    }
                    let scenes = this.window.document.getElementsByClassName('scene');
                    Array.from(scenes).forEach((sceneDiv, index) => {
                        sceneDiv.id = "scene_" + index;
                    })
                }
                if (this.deleteScenePanel.index === this.currentScene) {
                    if (this.currentScene === 0) {
                        this.currentScene++;
                    } else {
                        this.currentScene--;
                    }
                }

                this.scenes.splice(index, 1);
                this.deleteScenePanel.visible = false;
            },
            deleteScene(scene, index) {
                this.deleteScenePanel.index = index;
                this.deleteScenePanel.data = scene;
                this.deleteScenePanel.visible = true;
            },
            onOverlayDataChange(data, overlay) {

            },
            onOverlayInputChange(overlay) {

            },
            saveScenes() {
                let scenes = [];
                console.log(this.overlayIndexes, this.scenes);
                this.scenes.forEach((scene, sceneIndex) => {
                    let newScene = {
                        name: scene.name,
                        overlays: []
                    };
                    if (this.overlayIndexes[sceneIndex]) {
                        this.overlayIndexes[sceneIndex].forEach(overlayIndex => {
                            let overlay = scene.overlays[overlayIndex];
                            newScene.overlays.push({
                                title: overlay.title,
                                typeName: overlay.typeName,
                                data: overlay.object.data
                            })
                        })
                    }
                    scenes.push(newScene);
                })
                if (this.currentScenesListID) {
                    this.savingScenesLists = true;
                    this.$axios.put(`scenes/${this.currentScenesListID}`, {
                        data: scenes
                    }).then(res => {
                        this.savingScenesLists = false;
                        if (res.data.status) {

                        } else {
                            this.$store.commit('NEW_ALERT', res.data);
                        }
                    })
                } else {
                    this.addScenesListPanel.data.data = scenes;
                    this.addScenesListPanel.visible = true;
                }
            },
            toggleSection(overlay, section) {
                if (!overlay.closedSections) {
                    this.$set(overlay, 'closedSections', {})
                }
                this.$set(overlay.closedSections, section.name, !overlay.closedSections[section.name]);
            },
            getInputsForOverlay(object) {
               if (object) {
                   return object.inputs;
                } else {
                    return [];
                }
            },
            editSceneOverlay(overlay) {
                this.$set(overlay, 'settingsVisible', !overlay.settingsVisible);
            },
            deleteOverlay() {
                this.deleteOverlayPanel.loading = true;
                this.$axios.delete(`overlays/${this.deleteOverlayPanel.item.id}`).then(res => {
                    this.deleteOverlayPanel.loading = false;
                    this.$store.commit('NEW_ALERT', res.data);
                    this.deleteOverlayPanel.visible = false;
                    if (res.data.status) {
                        this.library.list = this.library.list.filter(item => item.id !== this.deleteOverlayPanel.item.id);
                    }
                })
            },
            openOverlayDeletePanel(item) {
                this.deleteOverlayPanel.item = item;
                this.deleteOverlayPanel.visible = true;
            },
            getOverlayTitle(item) {
                if (item.title) {
                    return item.title;
                } else {
                    if (sourcesList[item.typeName] && item.object) {
                        return `${item.object.title}`;
                    } else {
                        return null;
                    }
                }
            },
            deleteOverlayFromList(overlay, index) {
                this.scenes[this.currentScene].overlays[index].object.delete();
                this.overlayIndexes[this.currentScene] = this.overlayIndexes[this.currentScene].filter(overlayIndex => overlayIndex !== index);
                this.scenes[this.currentScene].overlays.splice(index, 1);
            },
            toggleOverlay(item) {
                this.$set(item, 'disabled', !item.disabled);
                if (item.object) {
                    item.object.setDisabled(item.disabled);
                }
            },

            getDisabledState(item, data) {
                let res = getInputProperty(item, 'activeIf', data);
                if (res !== null) {
                    return !res;
                }
                return false;
            },

            openOverlayAddPanel(data) {
                let typeName = data.typeName;
                let object = new sourcesList[typeName];

                this.overlayPanel.overlay.id = null;
                this.overlayPanel.overlay.title = typeName;
                this.overlayPanel.overlay.object = object;

                this.overlayPanel.overlay.typeName = typeName;
                this.overlayPanel.overlay.component = object.componentName;
                this.$nextTick(() => {
                    object.componentInstance = this.$refs.preview_component;
                });

                this.overlayPanel.isEditing = false;
                this.overlayPanel.visible = true;
                this.overlayPanel.overlay.object.isPreviewMode = true;
                //this.setDefaultInputValues();
            },
            openOverlayEditPanel(item) {
                let typeName = item.type_name;
                let object = new sourcesList[typeName]();
                let data = JSON.parse(JSON.stringify(item.data));
                object.data = data;

                this.overlayPanel.overlay.title = item.title;
                this.overlayPanel.overlay.id = item.id;
                this.overlayPanel.overlay.object = object;
                this.overlayPanel.overlay.typeName = typeName;
                this.overlayPanel.overlay.component = object.componentName;
                this.overlayPanel.isEditing = true;
                this.overlayPanel.visible = true;

                this.overlayPanel.overlay.object.isPreviewMode = true
            },
            getOverlayIcon(item) {
                return item.overlay ? item.overlay.icon_html : '';
            },
            updateOverlayPreview() {

            },
            setDefaultInputValues() {
                let overlay = this.overlayPanel.overlay.object;
                if (!overlay) {
                    return;
                }
                this.overlayPanel.overlay.title = this.overlayPanel.overlay.typeName;
            },
            async startStopStream() {
                console.log('start/stop');
                if (!this.window) {
                    this.window = window.open("", 'Catcast.tv studio',`width=${this.settings.sizeX},height=${this.settings.sizeY}`);
                    this.window.focus();
                    this.window.document.write(`
                            <style>
                            body {margin: 0;padding: 0; background: #000;width:100vw;height:100vh;overflow:hidden;}
                            #instructions {position:absolute;color: #fff;background:#000;z-index:100000000000;font-family: sans-serif;font-size: 80px;display: flex;align-items: center;justify-content: center;width: 100%;height: 100%;}
                            .scene {position: absolute; top: 0; left: 0;width: 100%;height: 100%;opacity: 0;z-index:1;transition: opacity .35s; }
                            .scene--visible {opacity: 1; z-index:2;}
                            </style>
                            <title>Select this tab</title>
                            <div id='instructions' style='display:none'>1. Select this tab<br>2. Go to studio</div>
                            <div id='main'></div>
                    `);
                    this.window.document.close();
                    this.window.onbeforeunload = () => {
                       this.window = null;
                    };
                    let main = this.window.document.getElementById('main');
                    this.scenes.forEach((scene, index) => {
                        let sceneDiv = this.window.document.createElement('div');
                        sceneDiv.className = "scene";
                        if (index === this.currentScene) {
                            sceneDiv.classList.add("scene--visible");
                        }
                        sceneDiv.id = 'scene_' + index;
                        main.appendChild(sceneDiv);
                        scene.overlays.forEach(overlay => {
                            overlay.object.bindElement(sceneDiv);
                        })
                    });
                    const onFirstClick = () => {
                        this.window.document.removeEventListener('click', onFirstClick);
                        this.window.document.getElementById('instructions').style.display = 'none';
                        Array.from(this.window.document.getElementsByClassName('video_need_resume')).forEach(video => {
                            video.classList.remove('video_need_resume');
                            video.play();
                        })
                        Array.from(this.window.document.getElementsByClassName('iframe_need_resume')).forEach(iframe => {
                            iframe.classList.remove('iframe_need_resume');
                            iframe.src = iframe.dataset.src;
                        })
                        navigator.mediaDevices.getDisplayMedia({
                            cursor: 'never',
                            frameRate: 30,
                            video: {
                                width: this.settings.sizeX,
                            },
                            audio: {
                                autoGainControl: false,
                                echoCancellation: false,
                                googAutoGainControl: false,
                                noiceSuppression: false
                            }
                        }).then(stream => {
                            let audioTrack = stream.getAudioTracks()[0];
                            if (audioTrack) {
                                console.log('got track from browser, connecting');
                                let source = this.audio.ctx.createMediaStreamSource(new MediaStream([audioTrack]));
                                source.connect(this.audio.merger, 0, 0);
                                source.connect(this.audio.merger, 0, 1);
                                stream = new MediaStream([stream.getVideoTracks()[0], this.audio.destination.stream.getAudioTracks()[0]]);
                            } else {
                                console.log('no audio track, using ctx only');
                                stream = new MediaStream([stream.getVideoTracks()[0], this.audio.destination.stream.getAudioTracks()[0]]);
                            }
                            console.log('stream', stream);
                            this.broadcast.streamManager.setMediaStream(stream);
                            this.broadcast.streamManager.start();
                        })
                    }
                    this.window.document.addEventListener('click', onFirstClick);

                }
            },
            showHideOverlay($index) {
                this.$set(this.scenes[this.currentScene].overlays[$index], 'hidden', !this.scenes[this.currentScene].overlays[$index].hidden);
            },
            saveOverlay(overlay) {
                if (!overlay) {
                  overlay = this.overlayPanel.overlay;
                }
                return new Promise(resolve => {
                    let data = {
                        title: overlay.title,
                        type_name: overlay.typeName,
                        data: overlay.object.data
                    }
                    if (!this.overlayPanel.isEditing) {
                        data.channel_id = this.channel.id;
                    }
                    data.studio_version = 2;
                    this.overlayPanel.saving = true;
                    this.$axios({
                        method: this.overlayPanel.isEditing ? 'PUT' : 'POST',
                        url: this.overlayPanel.isEditing ? 'overlays/' + this.overlayPanel.overlay.id : 'overlays',
                        data
                    }).then(res => {
                        this.overlayPanel.saving = false;
                        this.$store.commit('NEW_ALERT', res.data);
                        if (res.data.status) {
                            if (!this.overlayPanel.isEditing) {
                                this.library.list.unshift(res.data.overlay);
                            } else {
                                this.library.list.forEach((overlayItem, $index) => {
                                    if (res.data.overlay.id === overlayItem.id) {
                                        this.$set(this.library.list, $index, res.data.overlay);
                                    }
                                })
                            }
                            resolve();
                            if (this.overlayPanel.isEditing) {
                                this.overlayPanel.visible = false;
                            }
                        }
                    });
                })
            },
            saveSettings() {
                this.settings = JSON.parse(JSON.stringify(this.settingsPanel.data));
                this.settingsPanel.visible = false;
            },
            openSettingsPanel() {
                if (!this.broadcast.live) {
                    this.settingsPanel.data = JSON.parse(JSON.stringify(this.settings));
                    this.settingsPanel.visible = true;
                }
            }
        },
        async mounted() {
            if (!this.error) {
                try {
                    this.audioCtx = new AudioContext();
                    this.audioDest = this.audioCtx.createMediaStreamDestination();
                } catch (e) {
                    console.log(e);
                }
                if (this.scenesLists.length > 0) {
                    let scenes = this.scenesLists[0].data;
                    console.log(scenes);
                    if (scenes.length > 0) {
                        this.currentScenesListID = this.scenesLists[0].id;
                        this.setCurrentScene(0);
                        this.updateOverlays(scenes);
                    }
                }
                this.library.list = this.libraryList;
                if (localStorage.broadcast_studio_settings) {
                    try {
                        let settings = JSON.parse(localStorage.broadcast_studio_settings);
                        this.settings.sizeX = parseInt(settings.sizeX) || 640;
                        this.settings.sizeY = parseInt(settings.sizeY) || 360;
                    } catch (e) {

                    }
                }
                this.initAudioCtx();
                this.initStreamManager();
            }
        },
        data() {
            return {
                audio: {
                    ctx: null,
                    merger: null
                },
                window: null,
                needResume: false,
                overlayIndexes: {},
                fps: 0,
                broadcast: {
                    mediaRecorder: null,
                    streamManager: null,
                    streamState: "NOT_STARTED"
                },
                scenesListsPanel: {
                    visible: false,
                },
                deleteScenesListPanel: {
                    loading: false,
                    visible: false,
                    data: null,
                },
                savingScenesLists: false,
                addScenesListPanel: {
                    data: {
                        data: [],
                        name: '',
                    },
                    loading: false,
                    visible: false,
                },
                currentScenesListID: null,
                addScenePanel: {
                    data: {
                        name: '',
                    },
                    visible: false,
                },
                deleteScenePanel: {
                    index: -1,
                    visible: false,
                    data: null,
                },

                scenes: [
                    {
                        name: 'Scene 1',
                        overlays: []
                    },
                ],
                audioCtx: null,
                audioDest: null,
                settings: {
                    sizeX: 640,
                    sizeY: 360,
                },
                settingsPanel: {
                    visible: false,
                    data: null,
                },
                addOverlayMenuVisible: false,
                overlayPanel: {
                    isEditing: false,
                    saving: false,
                    visible: false,
                    addToLibrary: false,
                    overlay: {
                        title: '',
                        component: null,
                        object: null
                    },
                },
                currentScene: 0,
                output: null,
                library: {
                    loading: false,
                    list: this.libraryList,
                },
                previewImage: {
                    loaded: false,
                    object: null,
                },
                deleteOverlayPanel: {
                    visible: false,
                    item: null,
                    loading: false,
                },
                audio: {
                    analyser: null,
                    volume: 0,
                },
            }
        }
        ,
        async asyncData({app, params, redirect}) {
            let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
            if (channelData.status) {
                if (channelData.data.is_radio) {
                    return redirect(`/radio-studio/${params.id}`);
                }
                let channel = channelData.data;
                let id = params.id;
                let permissions = (await app.$axios.post(`/channels/${params.id}/getpermissions`)).data;
                if (Object.keys(permissions).length > 0) {
                    let library = (await app.$api.get(`overlays/getbychannel/${params.id}?studio_version=2`));
                    if (!library.status) {
                        return {
                            error: library
                        }
                    }
                    let keyData = (await app.$axios.post(`/channels/${params.id}/getstreamkey`)).data;
                    if (!keyData.status) {
                        return {
                            error: keyData
                        }
                    }
                    let scenes = (await app.$api.get(`scenes/getbychannel/${params.id}?studio_version=2`));
                    if (!scenes.status) {
                        return {
                            error: scenes
                        }
                    }
                    return {
                        scenesLists: scenes.data.list,
                        keyData,
                        error: false,
                        libraryList: library.list,
                        channel,
                        id,
                        permissions
                    };
                } else {
                    return {
                        error:
                            {
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
            StudioPicturePicker,
            StudioConferenceManager,
            CameraSource,
            IframeSource,
            ConferenceSource,
            TextSource,
            PictureSource,
            Chat,
            draggable
        },
        head() {
            return {
                title: this.$t('studio.heading'),
            }
        }
    }
</script>
<style lang="scss">
  .studio {
    display: flex;
    flex-direction: column;
    height: 100%;
    &__main-field {
      width: 100%;
      height: 100%;
      background: #000;
      position: relative;
      overflow: hidden;
    }
    &__scene-instance {
      width: 100%;
      height: 100%;
      position: absolute;
      transition: opacity .35s;
      opacity: 0;
      z-index: 1;
      &--visible {
        z-index: 2;
        opacity: 1;
      }
    }
    &__overlay {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
    &__container {
      height: 100%;
    }

    &__outer {
      height: 100%;
    }

    &__input-container {
      margin: 1.75em 0;

      &:first-of-type {
        margin: 1.25em 0 1.75em;
      }

      &:last-of-type {
        margin: 1.75em 0 .25em;
      }
    }

    &__main {
      display: flex;
      height: calc(100% - 5em);
      flex: 1;
    }

    &__scenes {
      padding: 1em;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--box-element-color);

      &__list {
        padding: .75em;
        font-size: .875em;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
      }
    }

    &__scene {
      border-radius: .5em;
      margin: 0 .5em 0 0;
      background: var(--title-box-color);
      cursor: pointer;
      display: flex;
      align-items: center;

      &__name {
        white-space: nowrap;
        display: block;
        padding: .75em 1.25em;
      }

      &__icon {
        display: block;
        padding: .75em 1.25em;

        &--delete {

        }
      }

      &:hover {
        filter: brightness(1.1);
      }

      &--active {
        background: var(--active-color);
      }
    }

    &__chat {
      flex: 1;
      overflow: auto;
    }

    &__preview {
      height: 100%;
      display: flex;
      flex-direction: column;
      background: var(--box-color);

      &__resize-block {
        pointer-events: none;
        position: absolute;
        border: 1px solid #fff;
        box-sizing: border-box;

        &__inner {
          position: relative;
          width: 100%;
          height: 100%;
        }

        &__handle {
          width: .5em;
          height: .5em;
          position: absolute;
          background: #fff;
          bottom: -.25em;
          right: -.25em;
          border-radius: 50%;
        }


      }

      &__video {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;

        canvas {
          width: 100%;
          height: auto;
        }
      }

      &__buttons {
        background: var(--box-footer-color);
        padding: 1em;
      }

      &__audio-meter-container {
        padding: 1em 1em 0;
      }

      &__audio-meter {
        background: var(--input-bg-color);
        height: 1em;
        border-radius: .25em;
        overflow: hidden;

        &__bar {
          height: 100%;
          background: var(--active-color);
        }
      }
    }

    &__controls {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    &__overlay-input-container {
      margin-bottom: -1em !important;
    }

    &__overlay-input-container:only-of-type .select {
      margin: 2em 0 0;
    }

    &__overlays {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;

      &__inner {
        flex: 1;
        padding: .5em;
        font-size: .875em;
        overflow: auto;

        .box__header__title {
          font-size: 1em;
          white-space: nowrap;
          max-width: 16em;
          overflow: hidden;
          text-overflow: ellipsis;
        }
      }

      &__header {
        display: flex;
        padding: 1em;
        background: var(--title-box-color);
        position: relative;

        &__text {
          margin: 0 1em 0 0;
        }

        &__buttons {

        }
      }

      &__bottom {
        background: var(--box-footer-color);
        font-size: 1.125em;
        &__inner {
          padding: 1em;
          position: relative;
        }
      }
    }

    &__right {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    &__library {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 30%;

      &__header {
        background: var(--box-color);
        display: flex;
        align-items: center;

        &__text {
          margin: 0 .5em 0 0;
        }
      }

      &__list {
        flex: 1;
        position: relative;
        padding: 0;
        max-height: calc(100% - 6em);
        background: var(--title-box-color);

        &__inner {
          display: flex;
          flex-wrap: wrap;
          height: auto;
          overflow: auto;
          max-height: 100%;
        }
      }


      &__item {
        width: 100%;

        &__picture {
          width: 100%;
          height: 1.35em;
        }

        &__inner {
          cursor: pointer;
          text-align: center;
          margin: .5em .5em 0;
          padding: .5em;
          position: relative;
          background: rgba(255, 255, 255, .1);
          display: flex;
          align-items: center;
        }

        &__title {
          font-weight: 600;
          overflow: hidden;
          text-overflow: ellipsis;
          font-size: .875em;
        }

        &__icon {
          margin: 0 .25em;
          width: 1.5em;
          text-align: left;
          font-size: 2em;

          i {
            opacity: .25;
          }
        }

        &__add {
          position: absolute;
          transition: all .4s;
          z-index: 1;
          opacity: .75;
          top: .5em;
          left: .5em;
          font-size: 1.25em;
          cursor: pointer;
          border-radius: .25em;
          background: rgba(0, 0, 0, 0.5);
          padding: .5em .5em .25em;

          &:hover {
            opacity: .95;
          }
        }

        &__button {
          cursor: pointer;
          position: absolute;
          top: 1em;
          opacity: .5;
          z-index: 1;
          transition: all .4s;

          &--delete {
            right: 1em;
          }

          &--edit {
            right: 3em;
          }

          &:hover {
            opacity: .75;
          }
        }
      }

    }

    @media screen and (max-width: 768px) {
      &__main {
        flex-direction: column-reverse;
      }

      &__right {
        width: 100%;
      }

      &__library__item {
        width: calc(100% / 3);
      }

      &__scenes {
        flex-direction: column;
        align-items: flex-start;
        padding: 0;

        &__list {
          padding: 1em;
          flex-wrap: wrap;
          overflow: auto;
        }
        &__buttons {
          width: 100%;
          padding: 1em;
          background: rgba(255, 255, 255, .05);
        }
      }
      &__scene {
        margin: 0 .5em .5em 0;
      }

      &__overlay-input-container {
        width: 100%;
      }
    }
  }

  .overlay-panel {
    display: flex;
    margin: .5em 0 0;

    &__description {
      background: var(--box-element-color);
      margin: 1em 0 2.5em;
      font-size: .875em;
      padding: 1em;
      border-radius: .25em;
    }

    &__preview {
      overflow: hidden;
      position: relative;
      margin: 0 1em 0 0;
      background: #000;
    }

    &__inputs {
      overflow: auto;
      padding: 0 1em 0 0;
      max-height: 60vh;

      &__section {
        margin: 0 0 1em;

        &__name {
          font-size: 1.25em;
          margin: 0 0 .25em;
          font-weight: 600;
        }
      }

      .filepicker__picture-block {
        max-height: 10em;
      }


    }

    &__vertical-delimiter {
      margin: .5em 0;
    }
  }

  .box--scene-overlay {
    margin: 0 0 .25em;

    .filepicker__picture-block {
      max-height: 7em;
    }

    .box__inner {
      padding: .5em;
    }

    &__section {
      margin: 0 0 .5em;

      &__header {
        display: flex;
        justify-content: space-between;
        padding: .5em;
        background: rgba(255, 255, 255, .05);
      }

      &:last-of-type {
        margin: 0;
      }

      &__button {
        background: rgba(255, 255, 255, .05);
        padding: .1em .5em;
        cursor: pointer;
        border-radius: .25em;
      }
    }
  }

  .source-panel {
    &__loading {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &__inputs {
      .modal__input-container {
        padding: 1.75em 0 0;
      }
    }

    &__preview {
      margin: 1em 0 0;

      video {
        background: #2b2b2b;
      }
    }
  }

</style>
