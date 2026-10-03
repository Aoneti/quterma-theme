/**
 * Gutenberg Editor Sidebar Panel for Quterma Editorial Meta
 * Provides native controls in Document Settings sidebar for Interview hero & role
 *
 * @package Quterma
 */
(function (wp) {
  if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components || !wp.data) {
    return;
  }

  var el = wp.element.createElement;
  var registerPlugin = wp.plugins.registerPlugin;
  var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
  var TextControl = wp.components.TextControl;
  var useSelect = wp.data.useSelect;
  var useDispatch = wp.data.useDispatch;

  function QutermaEditorialPanel() {
    var meta = useSelect(function (select) {
      return select('core/editor').getEditedPostAttribute('meta') || {};
    }, []);

    var editPost = useDispatch('core/editor').editPost;

    var person = meta._iv_person || '';
    var role = meta._iv_role || '';
    var subtitle = meta._page_subtitle || '';

    var currentPostType = useSelect(function (select) {
      return select('core/editor').getCurrentPostType();
    }, []);

    return el(
      PluginDocumentSettingPanel,
      {
        name: 'quterma-editorial-panel',
        title: 'Параметры материала (Кутерьма)',
        icon: 'format-chat',
        initialOpen: true,
      },
      el(
        'div',
        { style: { marginBottom: '16px' } },
        el(
          'p',
          { style: { fontSize: '12px', color: '#64748b', margin: '0 0 10px 0', lineHeight: '1.4' } },
          'Заполняется для интервью и спецматериалов. Выводится в шапке статьи и на карточках.'
        ),
        el(TextControl, {
          label: 'Имя и фамилия героя интервью',
          value: person,
          placeholder: 'Например: Андрей Данилов',
          help: 'Если не заполнено, используется заголовок статьи.',
          onChange: function (val) {
            editPost({ meta: { _iv_person: val } });
          },
        }),
        el(TextControl, {
          label: 'Род занятий / должность / регалии',
          value: role,
          placeholder: 'Например: архитектор-реставратор, краевед',
          onChange: function (val) {
            editPost({ meta: { _iv_role: val } });
          },
        }),
        currentPostType === 'page'
          ? el(TextControl, {
              label: 'Подзаголовок страницы',
              value: subtitle,
              placeholder: 'Краткий лид или описание раздела…',
              onChange: function (val) {
                editPost({ meta: { _page_subtitle: val } });
              },
            })
          : null
      )
    );
  }

  registerPlugin('quterma-editorial-sidebar', {
    render: QutermaEditorialPanel,
    icon: 'format-chat',
  });
})(window.wp);
