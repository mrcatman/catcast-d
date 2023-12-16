<template>
  <div class="studio__container">
    <div class="studio__outer" v-if="!error">
      <!-- <c-preloader block  v-if="devices.loading" />
       </div> -->
      <div class="studio">
        <div class="studio__main">
          <div class="studio__controls">

            <!-- <div class="studio__inputs">
               <div class="box">
                 <div class="studio__input-container">
                   <c-select :title="$t('studio.devices.audio')" :options="devices.audio" v-model="scenes[currentScene].audioDevice" />
                 </div>
                 <div class="studio__input-container">
                   <c-select @change="(e) => {onDeviceChange(e, 'video')}" :title="$t('studio.devices.video')" :options="devices.video" v-model="scenes[currentScene].videoDevice" />
                 </div>
               </div>
             </div> -->
            <div class="studio__overlays">

              <c-modal
                :header="overlayPanel.isEditing ? $t('studio.library.edit_overlay') : $t('studio.library.add_new_overlay')"
                v-model="overlayPanel.visible">
                <div slot="main">
                  <div class="overlay-panel">
                    <div class="overlay-panel__preview">
                      <canvas :style="{width: settings.sizeX+'px', height: settings.sizeY+'px'}"
                              ref="preview_canvas"></canvas>
                    </div>
                    <div class="overlay-panel__inputs"> <!-- :style="{height: settings.sizeY+'px'}" -->
                      <div v-if="getOverlayDescription" class="overlay-panel__description">
                        {{getOverlayDescription}}
                      </div>
                      <c-input :title="$t('studio.library.title')" v-model="overlayPanel.overlay.title"/>
                      <div class="vertical-delimiter overlay-panel__vertical-delimiter"></div>
                      <div class="overlay-panel__inputs__section" :key="$index"
                           v-for="(section, $index) in getInputsForOverlayPanel">
                        <div class="overlay-panel__inputs__section__name">{{$t(section.name)}}</div>
                        <div class="overlay-panel__inputs__section__contents">
                          <div class="row row--centered row--vertical-mobile" :key="$index2"
                               v-for="(row, $index2) in section.inputs">
                            <div class="col studio__overlay-input-container"
                                 :class="{'col--auto': (input.type === 'color' && ($index3 === 0 || row.length - 1 === $index3))}"
                                 :key="$index3" v-for="(input, $index3) in row">
                              <c-input :min="getInputProperty(input, 'min')" :max="getInputProperty(input, 'max')"
                                       :disabled="getDisabledState(input, overlayPanel.overlay.data)"
                                       :slider="input.slider" :title="$t(input.name)"
                                       v-model="overlayPanel.overlay.data[input.id]" v-if="input.type === 'input'"
                                       :type="input.inputType"/>
                              <c-colorpicker v-else-if="input.type === 'color'" :titleLeft="$t(input.name)"
                                             v-model="overlayPanel.overlay.data[input.id]"/>
                              <c-checkbox v-else-if="input.type === 'checkbox'" :title="$t(input.name)"
                                          v-model="overlayPanel.overlay.data[input.id]"/>
                              <c-picture-uploader :returnData="true" :big="true" folder="overlays"
                                            v-else-if="input.type === 'picture'" :title="$t(input.name)"
                                            v-model="overlayPanel.overlay.data[input.id]"/>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button color="green" :loading="overlayPanel.saving" v-if="!overlayPanel.isEditing"
                           @click="addOverlayToScene()">{{ $t('studio.add_overlay_to_scene')}}
                    </c-button>
                    <c-button v-if="overlayPanel.isEditing" :loading="overlayPanel.saving" @click="saveOverlay()">
                      {{!overlayPanel.isEditing ? $t('studio.add_overlay_to_library') : $t('global.save')}}
                    </c-button>
                    <c-checkbox v-if="!overlayPanel.isEditing" v-model="overlayPanel.addToLibrary"
                                :title="$t('studio.add_overlay_to_library')"/>
                  </div>
                </div>
              </c-modal>

              <c-modal :header="$t('studio.sources.add_new')" v-model="sourcePanel.visible">
                <div slot="main">
                  <div class="source-panel">
                    <div class="source-panel__inputs"> <!-- :style="{height: settings.sizeY+'px'}" -->
                      <div class="modal__input-container" v-if="sourcePanel.type_name === 'videoConferenceSource'">
                        <c-checkbox :title="$t('studio.sources.video_conference.own_camera')"
                                    v-model="sourcePanel.data.ownCamera"/>
                      </div>
                      <div
                        v-if="(sourcePanel.type_name === 'cameraSource' || (sourcePanel.type_name === 'videoConferenceSource' && sourcePanel.data.ownCamera)) && devices.loaded"
                        class="modal__input-container">
                        <c-select :title="$t('studio.devices.video')" :options="devices.video" :showEmptyVariant="true"
                                  v-model="sourcePanel.data.deviceId"
                                  @setVariant="(e) => {sourcePanel.data.deviceName = e.name}"/>
                      </div>
                      <div
                        v-if="(sourcePanel.type_name === 'cameraSource' || (sourcePanel.type_name === 'videoConferenceSource' && sourcePanel.data.ownCamera)) && devices.loaded"
                        class="modal__input-container">
                        <c-select :title="$t('studio.devices.audio')" :options="devices.audio" :showEmptyVariant="true"
                                  v-model="sourcePanel.data.audioDeviceId"
                                  @setVariant="(e) => {sourcePanel.data.audioDeviceName = e.name}"/>
                      </div>
                      <playlistPicker v-if="sourcePanel.type_name === 'playlistSource'" :channel="channel"
                                      v-model="sourcePanel.data.data"/>


                      <!--
                      <div class="modal__input-container" v-if="sourcePanel.type_name === 'screenCaptureSource'">
                        <c-checkbox :title="$t('studio.screen_capture.audio')" v-model="sourcePanel.data.captureAudio" />
                      </div>
                      -->
                      <div class="modal__input-container" v-if="sourcePanel.type_name === 'screenCaptureSource'">
                        <c-checkbox :title="$t('studio.sources.screen_capture.show_cursor')"
                                    v-model="sourcePanel.data.showCursor"/>
                      </div>
                    </div>
                    <div
                      v-show="sourcePanel.type_name === 'cameraSource' || (sourcePanel.type_name === 'videoConferenceSource' && sourcePanel.data.ownCamera)"
                      class="source-panel__preview">
                      <div v-if="devices.accessError" class="source-panel__access-error">
                        {{$t('studio.devices_access_error')}}
                      </div>
                      <div v-else-if="devices.loading" class="source-panel__loading">
                        <c-preloader/>
                        <div class="source-panel__text">{{$t('studio.loading_devices')}}</div>
                      </div>
                      <video v-show="sourcePanel.data.deviceId" v-else
                             :style="{width: settings.sizeX+'px', height: settings.sizeY+'px'}"
                             ref="preview_source"></video>
                    </div>
                  </div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <c-button
                    v-if="(sourcePanel.type_name !== 'cameraSource' && !(sourcePanel.type_name === 'videoConferenceSource' && sourcePanel.data.ownCamera)) || devices.loaded"
                    :loading="sourcePanel.loading" @click="addSource()">{{$t('global.add')}}
                  </c-button>
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

              <c-modal :header="$t('studio.resume._title')" v-model="needResume">
                <div slot="main">
                  <div class="modal__text">{{$t('studio.resume._text')}}</div>
                </div>
                <div class="modal__buttons" slot="buttons">
                  <div class="buttons-row">
                    <c-button @click="resumeContexts()">{{$t('global.ok')}}</c-button>
                  </div>
                </div>
              </c-modal>


              <c-modal :header="$t('studio.settings._title')" v-model="settingsPanel.visible">
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
                  <div class="row">
                    <div class="col">
                      <div class="modal__input-container">
                        <c-input :min="10" :max="60" :title="$t('studio.settings.fps')" v-model="settingsPanel.data.fps"
                                 type="number"/>
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
                  {{$t('studio.overlays._title')}}
                </span>
                <!--
                <div class="studio__overlays__header__buttons">
                  <c-button @click="sourcePanel.visible = true">{{$t('studio.sources._title')}}</c-button>
                </div>
                -->
              </div>

              <draggable v-model="overlayIndexes" :options="{handle: '.box--scene-overlay__drag'}"
                         class="studio__overlays__inner">
                <div :data-index="$index" v-if="scenes[currentScene].overlays[$index]"
                     class="box box--with-header box--scene-overlay"
                     :class="{'box--disabled': scenes[currentScene].overlays[$index].disabled}" :key="$index"
                     v-for="$index in overlayIndexes">
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
                  <div class="box__inner" v-show="scenes[currentScene].overlays[$index].settingsVisible"
                       v-if="!scenes[currentScene].overlays[$index]._hideInputs">
                    <div class="box--scene-overlay__section" :key="$index4"
                         v-for="(section, $index4) in getInputsForOverlay(scenes[currentScene].overlays[$index])">
                      <div v-show="!section.alwaysVisible" class="box--scene-overlay__section__header">
                        <div class="box--scene-overlay__section__name">{{$t(section.name)}}</div>
                        <div class="box--scene-overlay__section__buttons">
                          <a @click="toggleSection(scenes[currentScene].overlays[$index], section)"
                             class="box--scene-overlay__section__button">
                            <i class="fa fa-plus"
                               v-if="scenes[currentScene].overlays[$index].closedSections && scenes[currentScene].overlays[$index].closedSections[section.name]"></i>
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
                            <c-input @change="onOverlayInputChange(scenes[currentScene].overlays[$index])"
                                     :min="getInputProperty(input, 'min', scenes[currentScene].overlays[$index].data)"
                                     :max="getInputProperty(input, 'max', scenes[currentScene].overlays[$index].data)"
                                     :disabled="getDisabledState(input, scenes[currentScene].overlays[$index].data)"
                                     :slider="input.slider" :title="$t(input.name)"
                                     v-model="scenes[currentScene].overlays[$index].data[input.id]"
                                     v-if="input.type === 'input'" :type="input.inputType"/>
                            <c-colorpicker v-else-if="input.type === 'color'" :titleLeft="$t(input.name)"
                                           v-model="scenes[currentScene].overlays[$index].data[input.id]"/>
                            <c-checkbox v-else-if="input.type === 'checkbox'" :title="$t(input.name)"
                                        v-model="scenes[currentScene].overlays[$index].data[input.id]"/>
                            <c-picture-uploader :returnData="true" :big="true" folder="overlays"
                                          v-else-if="input.type === 'picture'" :title="$t(input.name)"
                                          v-model="scenes[currentScene].overlays[$index].data[input.id]"/>
                            <component @change="onOverlayInputChange(scenes[currentScene].overlays[$index])"
                                       @changeData="e => onOverlayDataChange(e, scenes[currentScene].overlays[$index])"
                                       :is="input.component" v-else-if="input.type === 'custom'"
                                       :data="scenes[currentScene].overlays[$index].data"
                                       :object="scenes[currentScene].overlays[$index].object" :channel="channel"
                                       v-model="scenes[currentScene].overlays[$index].data[input.id]"/>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </draggable>
              <div class="studio__overlays__bottom">
                <div class="studio__overlays__bottom__inner">
                  <c-button color="green" @click="addSourceMenuVisible = !addSourceMenuVisible">{{$t('global.add')}}</c-button>
                  <c-popup-menu class="popup-menu-top popup-menu--big" :manual="true" :visible="addSourceMenuVisible">
                    <c-popup-menu-item>{{$t('studio._tabs.library')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in library.list">
                          <div @click="addOverlay(item)" class="popup-menu__texts">
                            <span class="popup-menu__icon" v-html="getEffectIcon(item)"></span>
                            {{item.title}}
                          </div>
                          <div class="popup-menu__buttons">
                            <a @click="openOverlayEditPanel(item)" class="popup-menu__button"><i class="material-icons">edit</i></a>
                            <a @click="openOverlayDeletePanel(item)" class="popup-menu__button"><i
                              class="material-icons">clear</i></a>
                          </div>
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>
                    <c-popup-menu-item>{{$t('studio._tabs.overlays')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in getOverlaysList" @click="openOverlayPanel(item)">
                          <span class="popup-menu__icon" v-html="getEffectIcon(item)"></span>
                          {{item.title}}
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>
                    <c-popup-menu-item>{{$t('studio._tabs.sources')}}
                      <c-popup-menu class="popup-menu-right popup-menu-bottom-right">
                        <c-popup-menu-item :key="$index" v-for="(item, $index) in getSourcesList" @click="openSourcePanel(item)">
                          <span class="popup-menu__icon" v-html="getEffectIcon(item)"></span>
                          {{item.title}}
                        </c-popup-menu-item>
                      </c-popup-menu>
                    </c-popup-menu-item>
                  </c-popup-menu>
                </div>

              </div>
            </div>
          </div>
          <div class="studio__right">
            <div class="studio__preview">
              <video style="display:none" ref="camera_video"/>
              <div class="studio__preview__video">
                <div class="studio__preview__resize-block" v-show="positionBlock.visible"
                     :style="{'left': positionBlock.position.x + 'px', 'top': positionBlock.position.y + 'px', 'width': positionBlock.position.width + 'px', 'height': positionBlock.position.height + 'px'}">
                  <div class="studio__preview__resize-block__inner">
                    <div class="studio__preview__resize-block__handle" v-show="positionBlock.resizeVisible"></div>
                  </div>
                </div>
                <canvas @mousedown="e => onPreviewClick(e, false)" @click="e => onPreviewClick(e, true)"
                        ref="main_canvas"></canvas>
                <!-- :style="{width: settings.sizeX+'px', height: settings.sizeY+'px'}" -->
              </div>
              <div class="studio__preview__audio-meter-container">
                <div class="studio__preview__audio-meter">
                  <div class="studio__preview__audio-meter__bar" :style="{width: this.audio.volume+'%'}"></div>
                </div>
              </div>
              <div class="studio__preview__buttons">
                <div class="buttons-row">
                  <c-button :loading="broadcast.streamState === 'STARTING' || broadcast.streamState === 'STOPPING'"
                         :class="{'button--green': (broadcast.streamState !== 'STARTED'), 'button--red': (broadcast.streamState === 'STARTED' || broadcast.streamState === 'STOPPING')}"
                         @click="startStopStream()" icon="fa-video">{{(broadcast.streamState === 'STARTED' ||
                    broadcast.streamState === 'STOPPING') ? $t('studio.stop_broadcast') : $t('studio.start_broadcast')}}
                  </c-button>
                  <c-button :disabled="broadcast.streamState !== 'NOT_STARTED'" @click="openSettingsPanel()" icon="fa-cog">
                    {{$t('studio.settings._title')}}
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
              <div class="studio__scenes__list">
                <div class="studio__scene" :key="$index" :class="{'studio__scene--active': currentScene === $index}"
                     v-for="(scene, $index) in scenes">
                  <span class="studio__scene__name" @click="setCurrentScene($index)">{{scene.name}}</span>
                  <span @click="deleteScene(scene, $index)" v-if="scenes.length > 1"
                        class="studio__scene__icon studio__scene__icon--delete">
                <i class="fa fa-times"></i>
              </span>
                </div>
                <div class="studio__scene studio__scene--add" @click="showAddScenePanel()">
              <span class="studio__scene__icon studio__scene__icon--add">
                <i class="fa fa-plus"></i>
              </span>
                </div>
              </div>
              <div class="studio__scenes__buttons">
                <div class="buttons-row">
                  <c-button :loading="savingScenesLists" flat @click="saveScenes()">{{$t('studio.save_scenes')}}</c-button>
                  <c-button @click="scenesListsPanel.visible = true">{{$t('studio.load_scenes')}}</c-button>
                </div>
              </div>
            </div>
          </div>
          <div class="studio__library" style="display:none">
            <div class="studio__library__header">
              <c-tabs small v-model="currentTab" :data="tabs"/>
            </div>
            <div class="studio__library__list" v-if="currentTab === 'library'">
              <c-preloader block v-if="library.loading"/>
              <c-nothing-found :title="$t('studio.library.nothing_found._title')"
                               :text="$t('studio.library.nothing_found._text')"
                               v-else-if="library.list && library.list.length === 0"/>
              <div class="studio__library__list__inner" v-else>
                <div :key="$index" class="studio__library__item" v-for="(item, $index) in library.list">
                  <div class="studio__library__item__inner">
                    <!-- <a @click="addOverlay(item)" class="studio__library__item__add"><i class="material-icons">add_to_queue</i></a> -->
                    <a @click="openOverlayEditPanel(item)"
                       class="studio__library__item__button studio__library__item__button--edit"><i
                      class="material-icons">edit</i></a>
                    <a @click="openOverlayDeletePanel(item)"
                       class="studio__library__item__button studio__library__item__button--delete"><i
                      class="material-icons">clear</i></a>
                    <div @click="addOverlay(item)" class="studio__library__item__icon"
                         v-html="getEffectIcon(item)"></div>
                    <div @click="addOverlay(item)" class="studio__library__item__title">{{item.title}}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="studio__library__list" v-else-if="currentTab === 'overlays'">
              <div class="studio__library__list__inner">
                <div :key="$index" class="studio__library__item" v-for="(item, $index) in getOverlaysList"
                     @click="openOverlayPanel(item)">
                  <div class="studio__library__item__inner">
                    <div class="studio__library__item__icon" v-html="getEffectIcon(item)"></div>
                    <div class="studio__library__item__title">{{item.title}}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="studio__library__list" v-else-if="currentTab === 'sources'">
              <div class="studio__library__list__inner">
                <div :key="$index" class="studio__library__item" v-for="(item, $index) in getSourcesList"
                     @click="openSourcePanel(item)">
                  <div class="studio__library__item__inner">
                    <div class="studio__library__item__icon" v-html="getEffectIcon(item)"></div>
                    <div class="studio__library__item__title">{{item.title}}</div>
                  </div>
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
    import hacktimer from 'hacktimer';

    import StreamManager from "@/helpers/studio/streammanager";

    import draggable from 'vuedraggable';

    import {formatPublishDate} from '@/helpers/dates.js';

    import playlistManager from '@/components/studio/playlistManager';
    import playlistPicker from '@/components/studio/PlaylistPicker';
    import videoConferenceManager from '@/components/studio/videoConferenceManager';
    import Chat from '@/components/Chat';

    import effectsList from '@/helpers/studio/effects.js';
    import sourcesList from '@/helpers/studio/sources.js';

    export default {
        middleware: 'auth',
        watch: {
            "sourcePanel.type_name"(newName) {
                if (newName !== 'cameraSource' && newName !== 'videoConferenceSource') {
                    delete this.sourcePanel.data.deviceId;
                    delete this.sourcePanel.data.deviceName;
                } else {
                    if (!this.devices.loaded) {
                        this.loadDevices();
                    }
                }
            },
            "sourcePanel.data.deviceId"(newId) {
                this.sourcePanel.object = new sourcesList.cameraSource();
                this.sourcePanel.object.init({
                    deviceId: newId,
                    deviceName: this.sourcePanel.data.deviceName
                }, this.$refs.preview_source, null, this.settings);
            },
            scenes: {
                handler: function (val, oldVal) {

                },
                deep: true
            },
            settings(newSettings) {
                let canvas = this.$refs.main_canvas;
                canvas.width = newSettings.sizeX;
                canvas.height = newSettings.sizeY;
                localStorage.broadcast_studio_settings = JSON.stringify(newSettings);
            },
            "overlayPanel.overlay.data": {
                handler: function (val, oldVal) {
                    this.updateOverlayPreview();
                },
                deep: true
            },
            "overlayPanel.visible"(isVisible) {
                if (isVisible && !this.overlayPanel.isEditing) {
                    this.setDefaultInputValues();
                } else {
                    if (!isVisible) {
                        this.overlayPanel.isEditing = false;
                    }
                }
            },
            "overlayPanel.overlay.type_name"(typeName, oldTypeName) {
                if (oldTypeName.length > 0 && !this.overlayPanel.isEditing) {
                    this.setDefaultInputValues();
                }
            },
            currentScene(newScene) {
                this.onDeviceChange(this.scenes[newScene].videoDevice, 'video');
            }
        },
        computed: {
            tabs() {
                return [
                    {id: 'library', name: this.$t('studio._tabs.library')},
                    {id: 'overlays', name: this.$t('studio._tabs.overlays')},
                    {id: 'sources', name: this.$t('studio._tabs.sources')},
                ];
            },
            getSourcesList() {
                let list = [];
                Object.keys(sourcesList).forEach(key => {
                    let source = new sourcesList[key]();
                    list.push({
                        title: this.$t(source.getTitle()),
                        type_name: source.getID()
                    })
                });
                return list;
            },
            getInputsForOverlayPanel() {
                return this.getInputsForOverlay(this.overlayPanel.overlay);
            },
            getOverlayDescription() {
                if (effectsList[this.overlayPanel.overlay.type_name]) {
                    return this.$t((new effectsList[this.overlayPanel.overlay.type_name]).getDescription());
                }
                return null;
            },
            getOverlaysList() {
                let list = [];
                Object.keys(effectsList).forEach(key => {
                    let effect = new effectsList[key]();
                    list.push({
                        title: this.$t(effect.getTitle()),
                        type_name: effect.getID()
                    })
                });
                return list;
            },
        },
        methods: {
            initStreamManager() {
                const key = this.keyData.key.stream_key;
                const server = this.keyData.stream_server;
                this.broadcast.streamManager = new StreamManager({
                    wsUrl: server,
                    wsQuery: `name=${this.channel.stream_name}&key=${key}`
                });
            },
            resumeContexts() {
                this.loopCtx.resume();
                this.audioCtx.resume();
                this.needResume = false;
            },
            async updateOverlays() {
                let scenes = this.scenes;
                this.overlayIndexes = [];
                scenes.forEach((scene, index) => {
                    scene.overlays = scene.overlays.filter(overlay => overlay);
                    scene.overlays.forEach((overlay, overlayIndex) => {
                        if (!overlay) {
                            return;
                        }
                        if (this.currentScene === index) {
                            this.overlayIndexes.push(overlayIndex);
                            if (effectsList[overlay.type_name]) {
                                let object = new effectsList[overlay.type_name];
                                object.setData(overlay.data, this.settings);
                                overlay.object = object;
                            } else {
                                if (sourcesList[overlay.type_name]) {
                                    overlay._hideInputs = true;
                                    let object = new sourcesList[overlay.type_name](this.updateAudioContext);
                                    object.init(overlay.data, null, this.audioCtx, this.settings).then((needUpdateAudio) => {
                                        overlay.object = object;
                                        overlay._hideInputs = false;
                                        if (needUpdateAudio) {
                                            this.$nextTick(() => {
                                                this.updateAudioContext();
                                            })
                                        }
                                    });
                                }
                            }
                        } else {
                            if (overlay.object) {
                                if (overlay.object.audios) {
                                    overlay.object.audios.forEach(audio => {
                                        audio.disconnect(this.audioDest);
                                        audio.disconnect(this.audio.analyser);
                                    })
                                } else {
                                    if (overlay.object.audio) {
                                        overlay.object.audio.disconnect(this.audioDest);
                                        overlay.object.audio.disconnect(this.audio.analyser);
                                    }
                                }
                                if (overlay.object.video) {
                                    overlay.object.video.pause();
                                    overlay.object.video.removeAttribute('src');
                                    overlay.object.video.load();
                                }
                            }
                            overlay = undefined;
                        }
                    });
                });
                this.updateAudioContext();
            },
            async setCurrentScene(index) {
                this.disableOverlays = true;
                let oldScene = this.scenes[this.currentScene];
                let overlays = [];
                this.overlayIndexes.forEach(overlayIndex => {
                    overlays.push(oldScene.overlays[overlayIndex]);
                });
                oldScene.overlays = overlays;
                this.currentScene = index;
                await this.updateOverlays();
                this.disableOverlays = false;
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
                this.disableOverlays = true;
                this.scenes = item.data;
                await this.updateOverlays();
                this.disableOverlays = false;
                this.scenesListsPanel.visible = false;
            },
            formatPublishDate,
            addScenesList() {
                this.addScenesListPanel.loading = true;
                let data = this.addScenesListPanel.data;
                data.channel_id = this.channel.id;
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
                this.scenes.push({
                    name: this.addScenePanel.data.name,
                    overlays: []
                });
                this.addScenePanel.visible = false;
            },
            showAddScenePanel() {
                this.addScenePanel.data.name = `Scene ${(this.scenes.length + 1)}`;
                this.addScenePanel.visible = true;
            },
            deleteSelectedScene() {
                if (this.deleteScenePanel.index === this.currentScene) {
                    if (this.currentScene === 0) {
                        this.currentScene++;
                    } else {
                        this.currentScene--;
                    }
                }
                this.scenes.splice(this.deleteScenePanel.index, 1);
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
                if (overlay && overlay.object && overlay.object.volumeNode) {
                    overlay.object.volumeNode.gain.value = overlay.object.data.data.volume / 100;
                }
                if (overlay && overlay.object && overlay.object.isCurrentDragObject) {
                    let canvas = this.$refs.main_canvas;
                    let xRatio = canvas.offsetWidth / canvas.width;
                    let yRatio = canvas.offsetHeight / canvas.height;
                    let x = overlay.data[overlay.object.dragData.inputsMap.x];
                    let y = overlay.data[overlay.object.dragData.inputsMap.y];
                    this.positionBlock.position.x = x * xRatio;
                    this.positionBlock.position.y = y * yRatio;
                    if (overlay.object.canResize) {
                        let width = overlay.data[overlay.object.dragData.inputsMap.width];
                        let height = overlay.data[overlay.object.dragData.inputsMap.height];
                        this.positionBlock.position.x = width * xRatio;
                        this.positionBlock.position.y = height * yRatio;
                    }
                }
            },
            onPreviewClick(e, isClick) {
                return;
                let xRatio = e.target.offsetWidth / e.target.width;
                let yRatio = e.target.offsetHeight / e.target.height;
                let realX = e.offsetX / xRatio;
                let realY = e.offsetY / yRatio;
                let foundBlock = false;
                let overlays = this.scenes[this.currentScene].overlays.reverse();
                overlays.forEach(overlay => {
                    if (overlay.object && overlay.object.canDrag && !foundBlock) {
                        if (!overlay.isSource || overlay.data.own_size) {

                            let width = overlay.object.dragData.size.width || overlay.data[overlay.object.dragData.inputsMap.width];
                            let height = overlay.object.dragData.size.height || overlay.data[overlay.object.dragData.inputsMap.height];
                            if (overlay.data[overlay.object.dragData.inputsMap.x] <= realX && realX <= overlay.data[overlay.object.dragData.inputsMap.x] + width && overlay.data[overlay.object.dragData.inputsMap.y] <= realY && realY <= overlay.data[overlay.object.dragData.inputsMap.y] + height) {

                                foundBlock = true;
                                if (!overlay.object.isCurrentDragObject || !isClick) {
                                    let resizeAreaWidth = 10;

                                    this.positionBlock.visible = true;
                                    if (overlay.object.canResize) {
                                        this.positionBlock.resizeVisible = true;
                                    }
                                    let startX = (realX - overlay.object.dragData.position.x);
                                    let startY = (realY - overlay.object.dragData.position.y);
                                    // startX = 0;
                                    // startY = 0;
                                    //console.log(realX, realY, overlay.object.dragData.position.x, overlay.object.dragData.position.y);
                                    let startWidth = overlay.data[overlay.object.dragData.inputsMap.width];
                                    let startHeight = overlay.data[overlay.object.dragData.inputsMap.height];
                                    overlay.object.isCurrentDragObject = true;
                                    this.positionBlock.position = {
                                        x: overlay.object.dragData.position.x * xRatio,
                                        y: overlay.object.dragData.position.y * yRatio,
                                        width: width * xRatio,
                                        height: height * yRatio,
                                    };


                                    let isResizing = ((overlay.data[overlay.object.dragData.inputsMap.x] + width - realX < resizeAreaWidth) && (overlay.data[overlay.object.dragData.inputsMap.y] + height - realY < resizeAreaWidth));

                                    let onpositionBlockMouseMove = e => {
                                        let newRealX = e.offsetX - startX * xRatio;
                                        let newRealY = e.offsetY - startY * yRatio;
                                        if (!isResizing) {
                                            overlay.data[overlay.object.dragData.inputsMap.x] = newRealX / xRatio;
                                            overlay.data[overlay.object.dragData.inputsMap.y] = newRealY / yRatio;
                                            this.positionBlock.position.x = newRealX;
                                            this.positionBlock.position.y = newRealY;
                                        } else {
                                            //console.log(newRealX, overlay.object.data[overlay.object.dragData.inputsMap.x]);
                                            let newWidth = (newRealX - (overlay.data[overlay.object.dragData.inputsMap.x] - startWidth) * xRatio);
                                            let newHeight = (newRealY - (overlay.data[overlay.object.dragData.inputsMap.y] - startHeight) * yRatio);
                                            overlay.data[overlay.object.dragData.inputsMap.width] = newWidth / xRatio;
                                            overlay.data[overlay.object.dragData.inputsMap.height] = newHeight / yRatio;
                                            overlay.object.dragData.size.width = newWidth / xRatio;
                                            overlay.object.dragData.size.height = newHeight / yRatio;
                                            this.positionBlock.position.width = newWidth;
                                            this.positionBlock.position.height = newHeight;
                                        }
                                    };
                                    this.$refs.main_canvas.addEventListener('mousemove', onpositionBlockMouseMove)
                                    window.addEventListener('mouseup', () => this.$refs.main_canvas.removeEventListener('mousemove', onpositionBlockMouseMove))
                                } else {
                                    overlay.object.isCurrentDragObject = false;
                                    this.positionBlock.visible = false;
                                }
                            }
                        }
                    }
                })
            },
            loadDevices() {
                this.devices.loading = true;
                navigator.mediaDevices.getUserMedia(
                    {
                        video: true,
                        audio: true
                    }
                ).then(stream => {
                    navigator.mediaDevices.enumerateDevices().then((devices) => {
                        let audioDevicesList = [];
                        let videoDevicesList = [];
                        let foundDevices = {};
                        let audioCount = 0;
                        let videoCount = 0;
                        devices.forEach(device => {
                            if (!foundDevices[device.deviceId]) {
                                if (device.kind === 'audioinput') {
                                    audioDevicesList.push({
                                        value: device.deviceId,
                                        name: device.label || `Audio ${audioCount++}`
                                    })
                                }
                                if (device.kind === 'videoinput') {
                                    videoDevicesList.push({
                                        value: device.deviceId,
                                        name: device.label || `Video ${videoCount++}`
                                    })
                                }
                                foundDevices[device.deviceId] = true;
                            }
                        });
                        this.devices.audio = audioDevicesList;
                        this.devices.video = videoDevicesList;
                        this.devices.loading = false;
                        this.devices.loaded = true;

                        if (this.scenes[this.currentScene].videoDevice) {
                            //this.onDeviceChange(this.scenes[this.currentScene].videoDevice, 'video')
                        }
                    })
                }).catch(e => {
                    this.devices.accessError = true;
                    this.devices.loading = false;
                    console.log(e.message);
                });
            },
            startVolumeAnalyser() {
                this.audio.analyser = this.audioCtx.createAnalyser();
                let analyser = this.audio.analyser;
                let javascriptNode = this.audioCtx.createScriptProcessor(2048, 1, 1);
                analyser.smoothingTimeConstant = 0.8;
                analyser.fftSize = 1024;
                analyser.connect(javascriptNode);
                javascriptNode.connect(this.audioCtx.destination);
                javascriptNode.onaudioprocess = () => {
                    let array = new Uint8Array(analyser.frequencyBinCount);
                    analyser.getByteFrequencyData(array);
                    let values = 0;

                    let length = array.length;
                    for (let i = 0; i < length; i++) {
                        values += (array[i]);
                    }

                    let average = values / length;
                    this.audio.volume = average;
                }
            },
            updateAudioContext() {
                this.$nextTick(() => {
                    this.$nextTick(() => {
                        let lastIndex = -1;

                        this.scenes[this.currentScene].overlays.forEach((overlay, index) => {
                            if (!overlay) {
                                return;
                            }
                            if (overlay.object && overlay.isSource && overlay.object.audioActive) {
                                lastIndex = index;
                            }
                        });
                        this.scenes[this.currentScene].overlays.forEach((overlay, index) => {
                            if (!overlay) {
                                return;
                            }
                            if (overlay.object) {
                                if (overlay.isSource) {
                                    if (overlay.object.audioActive) {
                                        if (overlay.object.audios) {
                                            overlay.object.audios.forEach(audio => {
                                                audio.connect(this.audio.analyser);
                                                audio.connect(this.audioDest);
                                            })
                                        } else {
                                            if (overlay.object.audio) {
                                                overlay.object.audio.connect(this.audio.analyser);
                                                //let zeroGain = this.audioCtx.createGain();
                                                //zeroGain.gain.value = 0;
                                                //overlay.object.audio.connect(zeroGain);
                                                //zeroGain.connect(this.audioDest);
                                                overlay.object.audio.connect(this.audioDest);
                                            }
                                            //  if (index === lastIndex) {

                                            //}
                                        }
                                    }
                                }
                            }
                        });
                    })
                    this.audioCtx.resume();
                })
            },
            addSource() {
                this.sourcePanel.loading = true;
                let source = new sourcesList[this.sourcePanel.type_name](this.updateAudioContext);
                let type_name = JSON.parse(JSON.stringify(this.sourcePanel.type_name));
                let data = JSON.parse(JSON.stringify(this.sourcePanel.data));

                source.init(data, null, this.audioCtx, this.settings).then((needUpdateAudio) => {
                    let overlay = {
                        isSource: true,
                        object: source,
                        type_name,
                        data
                    };
                    this.overlayIndexes.push(this.scenes[this.currentScene].overlays.length);
                    this.scenes[this.currentScene].overlays.push(overlay);
                    this.sourcePanel.visible = false;
                    this.sourcePanel.loading = false;
                    if (needUpdateAudio) {
                        this.$nextTick(() => {
                            this.updateAudioContext();
                        })
                    }
                });
            },
            saveScenes() {
                let scenes = JSON.parse(JSON.stringify(this.scenes));
                scenes = scenes.map(scene => {
                    let overlays = [];
                    scene.overlays = scene.overlays.map(overlay => {
                        overlay.object = null;
                        return overlay;
                    });
                    return scene;
                });
                let overlays = [];
                this.overlayIndexes.forEach(overlayIndex => {
                    overlays.push(scenes[this.currentScene].overlays[overlayIndex]);
                });
                scenes[this.currentScene].overlays = overlays;
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
                // localStorage.broadcast_studio_scenes = JSON.stringify(scenes);
            },
            toggleSection(overlay, section) {
                if (!overlay.closedSections) {
                    this.$set(overlay, 'closedSections', {})
                }
                this.$set(overlay.closedSections, section.name, !overlay.closedSections[section.name]);
            },
            getInputsForOverlay(overlay) {
                if (effectsList[overlay.type_name]) {
                    return (new effectsList[overlay.type_name]).getInputs();
                } else {
                    if (sourcesList[overlay.type_name]) {
                        return (new sourcesList[overlay.type_name]).getInputs();
                    } else {
                        return [];
                    }
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
                this.addSourceMenuVisible = false;
            },
            getOverlayTitle(item) {
                if (item.title) {
                    return item.title;
                } else {
                    if (effectsList[item.type_name]) {
                        return this.$t((new effectsList[item.type_name]).getTitle());
                    } else {
                        if (sourcesList[item.type_name] && item.object) {
                            return `${item.object.getSourceName()}`; //${this.$t(item.object.getTitle())}
                        } else {
                            return null;
                        }
                    }
                }
            },
            deleteOverlayFromList(overlay, index) {
                this.overlayIndexes = this.overlayIndexes.filter(overlayIndex => overlayIndex !== index);
                this.overlayIndexes = this.overlayIndexes.map(overlayIndex => {
                    if (overlayIndex >= index) {
                        return overlayIndex - 1;
                    }
                    return overlayIndex;
                });
                this.scenes[this.currentScene].overlays.splice(index, 1);
                if (overlay.isSource) {
                    if (overlay.object && overlay.object.audio) {
                        overlay.object.audio.disconnect(this.audioDest);
                        overlay.object.audio.disconnect(this.audio.analyser);
                    }
                    if (overlay.object && overlay.object.audios) {
                        overlay.object.audios.forEach(audio => {
                            audio.disconnect(this.audioDest);
                            audio.disconnect(this.audio.analyser);
                        });
                    }
                    overlay = undefined;
                    this.updateAudioContext();
                }
            },
            toggleOverlay(item) {
                this.$set(item, 'disabled', !item.disabled);
                if (item.object) {
                    item.object.setDisabled(item.disabled);
                }
            },
            async addOverlayToScene() {
                if (this.overlayPanel.addToLibrary) {
                    await this.saveOverlay();
                }
                this.addOverlay(this.overlayPanel.overlay);
                this.overlayPanel.visible = false;
            },
            addOverlay(item) {
                let overlay = JSON.parse(JSON.stringify(item));
                let object = new effectsList[overlay.type_name];
                object.setData(overlay.data, this.settings);
                overlay.object = object;
                overlay.isSource = false;
                this.overlayIndexes.push(this.scenes[this.currentScene].overlays.length);
                this.scenes[this.currentScene].overlays.push(overlay);
                this.addSourceMenuVisible = false;
            },
            getDisabledState(item, data) {
                let res = this.getInputProperty(item, 'activeIf', data);
                if (res !== null) {
                    return !res;
                }
                return false;
            },
            getInputProperty(item, fn, data) {
                if (item[fn]) {
                    if (typeof item[fn] === 'function') {
                        return item[fn]({
                            data: data || this.overlayPanel.overlay.data,
                            settings: this.settings
                        });
                    }
                    return item[fn];
                }
                return null;
            },
            openSourcePanel(item) {
                this.sourcePanel.type_name = item.type_name;
                this.sourcePanel.visible = true;
                this.addSourceMenuVisible = false;
            },
            openOverlayPanel(item) {
                this.overlayPanel.overlay.type_name = item.type_name;
                this.overlayPanel.object = new effectsList[item.type_name];
                this.setDefaultInputValues();
                this.overlayPanel.isEditing = false;
                this.overlayPanel.visible = true;
                this.overlayPanel.object.is_preview = true;
                this.$nextTick(() => {
                    this.updateOverlayPreview();
                })
                this.addSourceMenuVisible = false;
            },
            openOverlayEditPanel(item) {
                this.$set(this.overlayPanel, 'overlay', JSON.parse(JSON.stringify(item)));
                this.overlayPanel.isEditing = true;
                this.overlayPanel.visible = true;
                this.overlayPanel.object = new effectsList[this.overlayPanel.overlay.type_name];
                this.overlayPanel.object.is_preview = true;
                this.$nextTick(() => {
                    this.updateOverlayPreview();
                })
                this.addSourceMenuVisible = false;
            },
            getEffectIcon(item) {
                if (item.type_name === 'simplePicture' && item.data && item.data.picture) {
                    return `<div style="background: url(${item.data.picture.path}) no-repeat center center; background-size: contain;" class="studio__library__item__picture"></div>`;
                } else {
                    if (effectsList[item.type_name] && effectsList[item.type_name].icon) {
                        if (effectsList[item.type_name].iconType === 1) {
                            return `<i class='fa fa-${effectsList[item.type_name].icon}'></i>`;
                        } else {
                            return `<i class='material-icons'>${effectsList[item.type_name].icon}</i>`;
                        }
                    } else {
                        if (sourcesList[item.type_name] && sourcesList[item.type_name].icon) {
                            if (sourcesList[item.type_name].iconType === 1) {
                                return `<i class='fa fa-${sourcesList[item.type_name].icon}'></i>`;
                            } else {
                                return `<i class='material-icons'>${sourcesList[item.type_name].icon}</i>`;
                            }
                        } else {
                            return `<i class='fa fa-question-circle'></i>`;
                        }
                    }
                }
            },
            updateOverlayPreview() {
                let canvas = this.$refs.preview_canvas;
                let object = this.overlayPanel.object;
                if (canvas && object) {
                    canvas.width = this.settings.sizeX;
                    canvas.height = this.settings.sizeY;

                    let ctx = canvas.getContext('2d');

                    ctx.fillStyle = "#000";
                    ctx.fillRect(0, 0, this.settings.sizeX, this.settings.sizeY);
                    if (!this.previewImage.loaded) {
                        if (!this.previewImage.object) {
                            this.previewImage.object = new Image();
                            this.previewImage.object.src = '/logo-preview.png';
                        }
                        this.previewImage.object.onload = () => {
                            this.previewImage.loaded = true;
                            object.setData(this.overlayPanel.overlay.data, this.settings);
                            ctx.drawImage(this.previewImage.object, 0, 0, this.settings.sizeX, this.settings.sizeY);
                            object.run(ctx);
                        }
                    } else {
                        object.setData(this.overlayPanel.overlay.data, this.settings);
                        ctx.drawImage(this.previewImage.object, 0, 0, this.settings.sizeX, this.settings.sizeY);
                        object.run(ctx);
                    }

                }
            },
            setDefaultInputValues() {
                if (effectsList[this.overlayPanel.overlay.type_name]) {
                    this.overlayPanel.object = new effectsList[this.overlayPanel.overlay.type_name];
                    this.overlayPanel.object.is_preview = true;
                    if (this.getInputsForOverlayPanel.length > 0) {
                        this.$set(this.overlayPanel.overlay, 'data', {});
                        this.getInputsForOverlayPanel.forEach(section => {
                            section.inputs.forEach(row => {
                                row.forEach(input => {
                                    this.$set(this.overlayPanel.overlay.data, input.id, this.getInputProperty(input, 'default'));
                                })
                            })
                        });
                        this.overlayPanel.overlay.title = this.overlayPanel.overlay.type_name;
                        this.$nextTick(() => {
                            this.updateOverlayPreview();
                        });
                    }
                } else {

                }
            },
            async startStopStream() {
                if (this.broadcast.streamState === "NOT_STARTED") {
                    //const key = this.keyData.key.stream_key;
                    //const wsReceiverServer = this.keyData.data.stream_server;

                    let videoStream = this.$refs.main_canvas.captureStream(this.settings.fps);
                    let audioStream = this.audioDest.stream;
                    let mediaStream = new MediaStream([videoStream.getVideoTracks()[0], audioStream.getAudioTracks()[0]]); //, dest.stream.getAudioTracks()[0]]
                    this.broadcast.streamManager.setMediaStream(mediaStream);
                    this.broadcast.streamManager.on('error', (e) => {
                        this.$store.commit('NEW_ALERT', {status: 0, text: 'global.server_error'});
                        this.broadcast.streamState = "NOT_STARTED";
                    })
                    this.broadcast.streamManager.start();
                    this.broadcast.streamState = "STARTING";
                    this.broadcast.streamManager.on('started', () => {
                        this.broadcast.streamState = "STARTED";
                    })
                } else {
                    this.broadcast.streamManager.stop();
                    this.broadcast.streamState = "STOPPING";
                    this.broadcast.streamManager.on('stopped', () => {
                        this.broadcast.streamState = "NOT_STARTED";
                    })
                }
            },
            showHideOverlay($index) {
                this.$set(this.scenes[this.currentScene].overlays[$index], 'hidden', !this.scenes[this.currentScene].overlays[$index].hidden);
            },
            saveOverlay() {
                return new Promise(resolve => {
                    let data = JSON.parse(JSON.stringify(this.overlayPanel.overlay));
                    if (!this.overlayPanel.isEditing) {
                        data.channel_id = this.channel.id;
                    }

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
            onDeviceChange(e, type) {

            },
            startAnimate() {
                let canvas = this.$refs.main_canvas;
                canvas.width = this.settings.sizeX;
                canvas.height = this.settings.sizeY;
                this.currentCtx = canvas.getContext('2d');

                const animate = () => {
                    this.currentCtx.fillStyle = "#000";
                    this.currentCtx.fillRect(0, 0, this.settings.sizeX, this.settings.sizeY);
                    //ctx.drawImage(video, 0, 0, videoSizeX, videoSizeY, 0, 0, this.settings.sizeX, this.settings.sizeY);
                    if (!this.disableOverlays) {
                        this.overlayIndexes.forEach(index => {
                            let overlay = this.scenes[this.currentScene].overlays[index];
                            if (overlay && overlay.object) {
                                if (overlay.isSource) {
                                    if (overlay.object.videoActive) {
                                        if (!overlay.disabled || overlay.object.canHandleDisabled) {
                                            if (overlay.object.multipleSources) {
                                                if (overlay.object.ownVideoSizes) {
                                                    overlay.object.videos.forEach((video, index) => {
                                                        this.currentCtx.drawImage(video, overlay.object.videoSizes[index].startX, overlay.object.videoSizes[index].startY, overlay.object.videoSizes[index].endX, overlay.object.videoSizes[index].endY, overlay.object.videoSizes[index].x, overlay.object.videoSizes[index].y, overlay.object.videoSizes[index].sizeX, overlay.object.videoSizes[index].sizeY);
                                                    })
                                                } else {
                                                    let videoWidth = this.settings.sizeX / overlay.object.videos.length;
                                                    overlay.object.videos.forEach((video, index) => {
                                                        this.currentCtx.drawImage(video, index * videoWidth, 0, videoWidth, this.settings.sizeY);
                                                    })
                                                }
                                            } else {
                                                this.currentCtx.drawImage(overlay.object.video, (overlay.data.own_size ? overlay.data.x : 0), (overlay.data.own_size ? overlay.data.y : 0), (overlay.data.own_size ? overlay.data.size_x : this.settings.sizeX), (overlay.data.own_size ? overlay.data.size_y : this.settings.sizeY));
                                            }
                                        }
                                    }
                                } else {
                                    if (!overlay.disabled || overlay.object.canHandleDisabled) {
                                        overlay.object.run(this.currentCtx);
                                    }
                                }
                            }
                        });
                    }
                }
                let audioTimerLoop = (callback, frequency) => {
                    let freq = frequency / 1000;
                    let aCtx = new AudioContext();
                    let silence = aCtx.createGain();
                    silence.gain.value = 0;
                    silence.connect(aCtx.destination);
                    onOSCend();

                    function onOSCend() {
                        let osc = aCtx.createOscillator();
                        osc.onended = onOSCend;
                        osc.connect(silence);
                        osc.start(0);
                        osc.stop(aCtx.currentTime + freq);
                        callback(aCtx.currentTime);
                    };
                    this.loopCtx = aCtx;
                    if (aCtx.state !== 'running') {
                        this.needResume = true;
                    }
                }
                audioTimerLoop(animate, 1000 / this.settings.fps);
                //setTimeout(animate, 1000 / this.settings.fps);
                //animate();
            },
            saveSettings() {
                this.settings = JSON.parse(JSON.stringify(this.settingsPanel.data));
                this.settingsPanel.visible = false;
            },
            openSettingsPanel() {
                if (!this.broadcast.streamState !== 'NOT_STARTED') {
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
                    this.startVolumeAnalyser();
                } catch (e) {
                    console.log(e);
                }
                if (this.scenesLists.length > 0) {
                    this.currentScenesListID = this.scenesLists[0].id;
                    let scenes = this.scenesLists[0].data;
                    this.currentScene = 0;
                    this.disableOverlays = true;
                    this.scenes = scenes;
                    await this.updateOverlays();
                    this.disableOverlays = false;
                }
                this.library.list = this.libraryList;
                if (localStorage.broadcast_studio_settings) {
                    try {
                        let settings = JSON.parse(localStorage.broadcast_studio_settings);
                        this.settings.sizeX = parseInt(settings.sizeX) || 640;
                        this.settings.sizeY = parseInt(settings.sizeY) || 360;
                        this.settings.fps = parseInt(settings.fps) || 30;
                    } catch (e) {

                    }
                }

                this.initStreamManager();
                this.devices.loading = true;
                this.$nextTick(() => {
                    this.startAnimate();
                });
            }
        },
        data() {
            return {

                needResume: false,
                loopCtx: null,
                overlayIndexes: [],
                currentCtx: null,
                popupWindow: null,
                fps: 0,
                broadcast: {
                    mediaRecorder: null,
                    streamManager: null,
                    streamState: "NOT_STARTED"
                },
                disableOverlays: false,
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
                positionBlock: {
                    visible: false,
                    resizeVisible: false,
                    position: {
                        x: 0,
                        y: 0,
                        width: 0,
                        height: 0
                    }
                },
                devices: {
                    loaded: false,
                    accessError: false,
                    loading: true,
                    loadingText: null,
                    audio: [],
                    video: [],
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
                    fps: 30,
                },
                settingsPanel: {
                    visible: false,
                    data: null,
                },
                overlayPanel: {
                    isEditing: false,
                    saving: false,
                    visible: false,
                    object: null,
                    addToLibrary: false,
                    overlay: {
                        title: '',
                        type_name: 'simpleText',
                        data: {},
                    },
                },
                sourcePanel: {
                    visible: false,
                    loading: false,
                    type_name: '',
                    data: {},
                    object: null,
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
                currentTab: 'library',
                audio: {
                    analyser: null,
                    volume: 0,
                },
                addSourceMenuVisible: false
            }
        },
        async asyncData({app, params, redirect}) {
            let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
            if (channelData.status) {
                if (channelData.data.is_radio) {
                    return redirect(`/radio-studio/${params.id}`);
                }
                let channel = channelData.data;
                let id = params.id;
                let permissions = (await app.$api.get(`/channels/${params.id}/permissions`)).data;
                if (Object.keys(permissions).length > 0) {
                    let library = (await app.$api.get(`overlays/getbychannel/${params.id}`));
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
                    let scenes = (await app.$api.get(`scenes/getbychannel/${params.id}`));
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
            videoConferenceManager,
            playlistManager,
            playlistPicker,
            Chat,
            draggable
        },
        head() {
            return {
                title: this.$t('studio._title'),
            }
        },
    }
</script>
<style lang="scss">
  .studio {
    display: flex;
    flex-direction: column;
    height: 100%;

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
      background: rgba(0, 0, 0, 0.5);

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
        background: #000;

        canvas {
          width: 100%;
          height: auto;
        }
      }

      &__buttons {
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
        padding: 1.125em;
        background: var(--box-footer-color);
        font-size: 1.125em;

        &__inner {
          position: relative;
        }
      }
    }

    &__right {
      flex: 2;
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
          max-width: 30vw;
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
      display: flex;
      align-items: center;
      margin: 0 1em 0 0;

      canvas {
        width: 100% !important;
        height: auto !important;
        background: #000;
      }
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
