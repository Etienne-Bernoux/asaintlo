/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports) {

(function (wp) {
  var registerBlockType = wp.blocks.registerBlockType; //Blocks API

  var createElement = wp.element.createElement; //React.createElement

  var __ = wp.i18n.__; //translation functions

  var InspectorControls = wp.editor.InspectorControls; //Block inspector wrapper

  var _wp$components = wp.components,
      PanelBody = _wp$components.PanelBody,
      SelectControl = _wp$components.SelectControl,
      ToggleControl = _wp$components.ToggleControl,
      RangeControl = _wp$components.RangeControl,
      ServerSideRender = _wp$components.ServerSideRender; //WordPress form inputs and server-side renderer

  var sonaarIcon = wp.element.createElement('svg', {
    width: 20,
    height: 20,
    viewBox: '0 0 512 512'
  }, wp.element.createElement('path', {
    d: "M470.38 1.51L150.41 96A32 32 0 0 0 128 126.51v261.41A139 139 0 0 0 96 384c-53 0-96 28.66-96 64s43 64 96 64 96-28.66 96-64V214.32l256-75v184.61a138.4 138.4 0 0 0-32-3.93c-53 0-96 28.66-96 64s43 64 96 64 96-28.65 96-64V32a32 32 0 0 0-41.62-30.49z"
  }));
  registerBlockType('sonaar/sonaar-block', {
    // Built-in attributes
    title: 'Sonaar MP3',
    icon: sonaarIcon,
    category: 'embed',
    keywords: ['mp3', 'player', 'audio', 'sonaar', 'podcast', 'music', 'beat', 'sermon', 'episode', 'radio', 'stream', 'sonar', 'sonaar', 'sonnaar', 'track'],
    // Built-in functions
    edit: function edit(props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var run_pro = attributes.run_pro;
      var album_id = attributes.album_id;
      var playlist_list = attributes.playlist_list;
      var playlist_show_playlist = attributes.playlist_show_playlist;
      var playlist_show_album_market = attributes.playlist_show_album_market;
      var playlist_hide_artwork = attributes.playlist_hide_artwork;
      var playlist_show_soundwave = attributes.playlist_show_soundwave;
      var play_current_id = attributes.play_current_id;
      var enable_sticky_player = false;
      var enable_shuffle = false;
      var enable_scrollbar = false;
      var scrollbar_height = 200;

      if (run_pro) {
        enable_sticky_player = attributes.enable_sticky_player;
        enable_shuffle = attributes.enable_shuffle;
        enable_scrollbar = attributes.enable_scrollbar;
        scrollbar_height = attributes.scrollbar_height;
      }

      var ironAudioplayersLoaded = false;
      var setIronAudioplayers = setTimeout(function () {
        if (jQuery('.iron-audioplayer').length > 0) {
          ironAudioplayersLoaded = true;
        } else {
          var setIronAudioplayerInterval = setInterval(function () {
            if (jQuery('.iron-audioplayer').length > 0) {
              IRON.players = [];
              jQuery('.iron-audioplayer').each(function () {
                var player = Object.create(IRON.audioPlayer);
                player.init(jQuery(this));
                IRON.players.push(player);
              });
              clearInterval(setIronAudioplayerInterval);
            }
          }, 500);
        }

        IRON.players = [];
        jQuery('.iron-audioplayer').each(function () {
          var player = Object.create(IRON.audioPlayer);
          player.init(jQuery(this));
          IRON.players.push(player);
        });
      }, 2000);

      if (!ironAudioplayersLoaded) {
        var setIronAudioplayersInterval = setInterval(function () {
          if (jQuery('.iron-audioplayer').length > 0) {
            /*post-title-0 is ID of Post Title Textarea*/
            //Actual functions goes here
            ironAudioplayersLoaded = true;
          }

          if (ironAudioplayersLoaded) {
            IRON.players = [];
            jQuery('.iron-audioplayer').each(function () {
              var player = Object.create(IRON.audioPlayer);
              player.init(jQuery(this));
              IRON.players.push(player);
            });
            clearInterval(setIronAudioplayersInterval);
          }
        }, 500);
      }

      var blockLoaded = false;
      var blockLoadedInterval = setInterval(function () {
        if (document.getElementById('playlist-list-id')) {
          /*post-title-0 is ID of Post Title Textarea*/
          //Actual functions goes here
          blockLoaded = true;
        }

        if (blockLoaded) {
          jQuery('#playlist-list-id').select2();
          var selectedData = [];
          var unselectedData = []; // jQuery('#playlist-list-id').on('select2:selecting select2:unselectingt', function (e) {

          jQuery('#playlist-list-id').on('select2:select', function (e) {
            var data = e.params.data;

            if ("" != album_id) {
              selectedData = album_id;
            }

            if (jQuery.inArray(data.id, selectedData) === -1) {
              selectedData.push(data.id);
              setAttributes({
                album_id: selectedData
              });
              setIronAudioplayers;
            }
          }).on('select2:unselect', function (e) {
            var data = e.params.data;

            if ("" != album_id) {
              unselectedData = album_id;
            }

            if (jQuery.inArray(data.id, unselectedData) !== -1) {
              unselectedData = jQuery.grep(unselectedData, function (value) {
                return value != data.id;
              });
              setAttributes({
                album_id: unselectedData
              });
              setIronAudioplayers;
            }
          });
          clearInterval(blockLoadedInterval);
        }
      }, 500); //Display block preview and UI

      return [createElement(InspectorControls, {
        key: 'inspector'
      }, createElement(PanelBody, {
        title: __('Player Settings', 'sonaar-music'),
        initialOpen: false
      }, createElement(SelectControl, {
        multiple: true,
        id: "playlist-list-id",
        options: playlist_list,
        value: album_id,
        onChange: function onChange(value) {
          setAttributes({
            album_id: value
          });
          setIronAudioplayers;
        }
      }), run_pro ? createElement(ToggleControl, {
        label: __('Enable Sticky Audio Player', 'sonaar-music'),
        checked: enable_sticky_player,
        onChange: function onChange(sticky_player) {
          setAttributes({
            enable_sticky_player: sticky_player
          });
          setIronAudioplayers;
        }
      }) : null, run_pro ? createElement(ToggleControl, {
        label: __('Enable Shuffle', 'sonaar-music'),
        checked: enable_shuffle,
        onChange: function onChange(shuffle) {
          setAttributes({
            enable_shuffle: shuffle
          });
          setIronAudioplayers;
        }
      }) : null, run_pro ? createElement(ToggleControl, {
        label: __('Enable Scrollbar', 'sonaar-music'),
        checked: enable_scrollbar,
        onChange: function onChange(scrollbar) {
          setAttributes({
            enable_scrollbar: scrollbar
          });
          setIronAudioplayers;
        }
      }) : null, run_pro && enable_scrollbar ? createElement(RangeControl, {
        label: __('Scrollbar Height (px)', 'sonaar-music'),
        value: scrollbar_height,
        min: 0,
        max: 2000,
        onChange: function onChange(value) {
          setAttributes({
            scrollbar_height: value
          });
          setIronAudioplayers;
        }
      }) : null, createElement(ToggleControl, {
        label: __('Show Playlist', 'sonaar-music'),
        checked: playlist_show_playlist,
        onChange: function onChange(show_playlist) {
          return setAttributes({
            playlist_show_playlist: show_playlist
          });
        }
      }), createElement(ToggleControl, {
        label: __('Show Album Store', 'sonaar-music'),
        checked: playlist_show_album_market,
        onChange: function onChange(show_album_market) {
          return setAttributes({
            playlist_show_album_market: show_album_market
          });
        }
      }), createElement(ToggleControl, {
        label: __('Hide Album Cover', 'sonaar-music'),
        checked: playlist_hide_artwork,
        onChange: function onChange(hide_artwork) {
          return setAttributes({
            playlist_hide_artwork: hide_artwork
          });
        }
      }), createElement(ToggleControl, {
        label: __('Hide Mini Player/Soundwave', 'sonaar-music'),
        checked: playlist_show_soundwave,
        onChange: function onChange(show_soundwave) {
          return setAttributes({
            playlist_show_soundwave: show_soundwave
          });
        }
      }), createElement(ToggleControl, {
        label: __('Play its own Post ID track', 'sonaar-music'),
        checked: play_current_id,
        onChange: function onChange(play_id) {
          return setAttributes({
            play_current_id: play_id
          });
        }
      }))), createElement(ServerSideRender, {
        block: 'sonaar/sonaar-block',
        attributes: {
          album_id: album_id,
          playlist_show_playlist: playlist_show_playlist,
          playlist_show_album_market: playlist_show_album_market,
          playlist_hide_artwork: playlist_hide_artwork,
          playlist_show_soundwave: playlist_show_soundwave,
          play_current_id: play_current_id,
          enable_sticky_player: enable_sticky_player,
          enable_shuffle: enable_shuffle,
          enable_scrollbar: enable_scrollbar,
          scrollbar_height: scrollbar_height
        }
      })];
    },
    save: function save() {
      return null; //save has to exist. This all we need
    }
  });
})(window.wp);

/***/ })

/******/ });
//# sourceMappingURL=index.js.map