//<?php
/**
 * setMultiLangPlaceholders
 *
 * Проставляем плейсхолдеры мультиязычности
 *
 * @category    plugin
 * @internal    @events OnChunkFormRender,OnTempFormRender,OnPageNotFound
 * @internal    @modx_category MultiLang
 * @internal    @properties {
  "mode": [
    {
      "label": "Используемый плагин мультияза",
      "type": "list",
      "value": "blang",
      "options": "blang,evobabel",
      "default": "",
      "desc": ""
    }
  ],
  "replace_all": [
    {
      "label": "Заменять выбранный текст везде",
      "type": "list",
      "value": "true",
      "options": "true,false",
      "default": "",
      "desc": ""
    }
  ],
  "translate": [
    {
      "label": "Автоматически переводить",
      "type": "list",
      "value": "true",
      "options": "true,false",
      "default": "",
      "desc": ""
    }
  ]
}
 * @internal    @disabled 0
 * @internal    @installset base
 */
require MODX_BASE_PATH.'assets/plugins/setMultiLangPlaceholders/plugin.setMultiLangPlaceholders.php';
