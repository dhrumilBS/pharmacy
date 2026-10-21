/* Block editor side of the guide components (see inc/blog-blocks.php; the markup on the site comes from PHP).
   No build step: plain ES5 with wp.element.createElement. Every block below is dynamic: it saves only its
   content (InnerBlocks) or its attributes, so a guide is portable and a component's look is changed in one place.

   Blocks: takeaways, note, rule, checklist, steps (+step), compare (+compare-row), sum (+sum-line), cta,
   faq (+faq-item). The sidebar panel "Article settings" holds the standfirst, topic, cover and dates. */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var __ = wp.i18n.__;
  var cfg = window.rxBlogEditor || { topics: {}, screens: {}, pages: {}, features: {} };
  var CAT = 'healthray-guide';
  var InnerBlocks = be.InnerBlocks;
  var RichText = be.RichText;
  var useBlockProps = be.useBlockProps;
  var InspectorControls = be.InspectorControls;

  function opts(map, first) {
    var o = first ? [{ label: first, value: '' }] : [];
    Object.keys(map || {}).forEach(function (k) { o.push({ label: map[k], value: k }); });
    return o;
  }
  function inner(allowed, template) {
    return el(InnerBlocks, { allowedBlocks: allowed || undefined, template: template || undefined, templateLock: false });
  }
  function save() { return el(InnerBlocks.Content); }
  function rich(props, key, tag, placeholder, formats) {
    return el(RichText, {
      tagName: tag || 'span', value: props.attributes[key] || '', placeholder: placeholder || '',
      allowedFormats: formats || [],
      onChange: function (v) { var o = {}; o[key] = v; props.setAttributes(o); }
    });
  }
  function panel(title, children) { return el(InspectorControls, null, el(c.PanelBody, { title: title, initialOpen: true }, children)); }
  function select(props, key, label, options) {
    return el(c.SelectControl, { label: label, value: props.attributes[key] || '', options: options, onChange: function (v) { var o = {}; o[key] = v; props.setAttributes(o); } });
  }
  function text(props, key, label) {
    return el(c.TextControl, { label: label, value: props.attributes[key] || '', onChange: function (v) { var o = {}; o[key] = v; props.setAttributes(o); } });
  }
  function reg(name, def) {
    def.apiVersion = 3; def.category = CAT; def.supports = Object.assign({ html: false }, def.supports || {});
    wp.blocks.registerBlockType(name, def);
  }
  function li(t) { return ['core/list-item', { content: t }]; }

  /* ---- In short ---- */
  reg('healthray/takeaways', {
    title: __('In short'), icon: 'editor-ol', description: __('The short answer. The first one in a guide is shown at the top of the page as "The short version".'),
    edit: function () {
      return el('aside', useBlockProps({ className: 'bk-take' }),
        el('p', { className: 'bk-take-h' }, __('In short (the first one is shown at the top of the guide)')),
        inner(['core/list'], [['core/list', { ordered: true }, [li(''), li(''), li('')]]]));
    },
    save: save
  });

  /* ---- Tip / Note / Watch out / Example ---- */
  var KINDS = { tip: __('Tip'), note: __('Note'), caution: __('Watch out'), example: __('Example'), verdict: __('Verdict') };
  reg('healthray/note', {
    title: __('Note: tip, watch out, example'), icon: 'info-outline', description: __('A boxed note. Pick the type in the sidebar.'),
    attributes: { kind: { type: 'string', default: 'tip' }, title: { type: 'string' } },
    edit: function (props) {
      var k = props.attributes.kind || 'tip';
      return el(Fragment, null,
        panel(__('Note'), select(props, 'kind', __('Type'), opts(KINDS))),
        el('aside', useBlockProps({ className: 'bk-note bk-note-' + k }),
          el('p', { className: 'bk-note-h' }, el('span', { className: 'bk-note-k' }, KINDS[k] || KINDS.note), rich(props, 'title', 'b', __('Optional heading'))),
          el('div', { className: 'bk-note-b' }, inner(['core/paragraph', 'core/list'], [['core/paragraph', { placeholder: __('Write the note…') }]]))));
    },
    save: save
  });

  /* ---- Rule of thumb ---- */
  reg('healthray/rule', {
    title: __('Rule of thumb'), icon: 'lightbulb', description: __('One rule, set large.'),
    attributes: { label: { type: 'string' } },
    edit: function (props) {
      return el('aside', useBlockProps({ className: 'bk-rule' }),
        el('p', { className: 'bk-rule-k' }, rich(props, 'label', 'span', __('Rule of thumb'))),
        el('div', { className: 'bk-rule-t' }, inner(['core/paragraph'], [['core/paragraph', { placeholder: __('Minimum = daily sales × days to next delivery + a cushion.') }]])));
    },
    save: save
  });

  /* ---- Checklist ---- */
  reg('healthray/checklist', {
    title: __('Checklist'), icon: 'yes-alt', description: __('A printable tick list. One list item per line.'),
    attributes: { title: { type: 'string' } },
    edit: function (props) {
      return el('figure', useBlockProps({ className: 'bk-check' }),
        el('p', { className: 'bk-check-h' }, rich(props, 'title', 'b', __('Checklist title')), el('span', { className: 'bk-print' }, __('Print this list'))),
        inner(['core/list'], [['core/list', {}, [li(''), li(''), li('')]]]));
    },
    save: save
  });

  /* ---- Steps ---- */
  reg('healthray/step', {
    title: __('Step'), icon: 'arrow-right-alt', parent: ['healthray/steps'], supports: { inserter: false },
    attributes: { title: { type: 'string' }, when: { type: 'string' } },
    edit: function (props) {
      return el('div', useBlockProps({ className: 'rx-ed-step' }),
        rich(props, 'title', 'p', __('Step title')),
        inner(['core/paragraph', 'core/list'], [['core/paragraph', { placeholder: __('What to do…') }]]),
        rich(props, 'when', 'p', __('When (optional), e.g. Week 1')));
    },
    save: save
  });
  reg('healthray/steps', {
    title: __('Steps'), icon: 'editor-ol', description: __('Numbered steps, each with a title and an optional "when" line.'),
    edit: function () {
      return el('div', useBlockProps({ className: 'rx-ed-box' }), el('p', { className: 'rx-ed-k' }, __('Steps')),
        inner(['healthray/step'], [['healthray/step'], ['healthray/step'], ['healthray/step']]));
    },
    save: save
  });

  /* ---- Comparison ---- */
  reg('healthray/compare-row', {
    title: __('Comparison row'), icon: 'minus', parent: ['healthray/compare'], supports: { inserter: false },
    attributes: { label: { type: 'string' }, a: { type: 'string' }, b: { type: 'string' } },
    edit: function (props) {
      return el('div', useBlockProps({ className: 'rx-ed-row' }),
        rich(props, 'label', 'p', __('Row label')), rich(props, 'a', 'p', __('Option A'), ['core/bold', 'core/italic', 'core/link']), rich(props, 'b', 'p', __('Option B'), ['core/bold', 'core/italic', 'core/link']));
    },
    save: function () { return null; }
  });
  reg('healthray/compare', {
    title: __('Comparison'), icon: 'columns', description: __('Two options side by side, with an optional winner and verdict.'),
    attributes: { title: { type: 'string' }, aName: { type: 'string' }, aTag: { type: 'string' }, bName: { type: 'string' }, bTag: { type: 'string' }, winner: { type: 'string' }, verdict: { type: 'string' } },
    edit: function (props) {
      return el(Fragment, null,
        panel(__('Comparison'), [
          text(props, 'aName', __('Option A name')), text(props, 'aTag', __('Option A tag (optional)')),
          text(props, 'bName', __('Option B name')), text(props, 'bTag', __('Option B tag (optional)')),
          select(props, 'winner', __('Winner'), [{ label: __('None'), value: '' }, { label: __('Option A'), value: 'a' }, { label: __('Option B'), value: 'b' }])
        ]),
        el('figure', useBlockProps({ className: 'rx-ed-box' }),
          rich(props, 'title', 'p', __('Comparison title (optional)')),
          el('p', { className: 'rx-ed-k' }, (props.attributes.aName || __('Option A')) + '  vs  ' + (props.attributes.bName || __('Option B'))),
          inner(['healthray/compare-row'], [['healthray/compare-row'], ['healthray/compare-row'], ['healthray/compare-row']]),
          el('div', { className: 'bk-cmp-v' }, el('b', null, __('Verdict.') + ' '), rich(props, 'verdict', 'span', __('One or two sentences.'), ['core/bold', 'core/italic', 'core/link']))));
    },
    save: save
  });

  /* ---- Worked sum ---- */
  var OPS = [{ label: __('Auto (× after the first)'), value: 'auto' }, { label: __('None'), value: 'none' }, { label: '×', value: '×' }, { label: '+', value: '+' }, { label: '−', value: '−' }, { label: '÷', value: '÷' }, { label: '=', value: '=' }];
  reg('healthray/sum-line', {
    title: __('Sum line'), icon: 'minus', parent: ['healthray/sum'], supports: { inserter: false },
    attributes: { label: { type: 'string' }, value: { type: 'string' }, op: { type: 'string', default: 'auto' } },
    edit: function (props) {
      return el(Fragment, null,
        panel(__('Line'), select(props, 'op', __('Sign before the value'), OPS)),
        el('div', useBlockProps({ className: 'rx-ed-row rx-ed-row-2' }), rich(props, 'label', 'p', __('Label, e.g. Daily sales')), rich(props, 'value', 'p', __('Value, e.g. 12 strips'))));
    },
    save: function () { return null; }
  });
  reg('healthray/sum', {
    title: __('Worked sum'), icon: 'calculator', description: __('A calculation written out line by line, with a result.'),
    attributes: { title: { type: 'string' }, resultLabel: { type: 'string' }, resultValue: { type: 'string' }, note: { type: 'string' } },
    edit: function (props) {
      return el('figure', useBlockProps({ className: 'rx-ed-box' }),
        el('p', { className: 'rx-ed-k' }, __('Worked example · illustrative numbers')),
        rich(props, 'title', 'p', __('Sum title (optional)')),
        inner(['healthray/sum-line'], [['healthray/sum-line'], ['healthray/sum-line'], ['healthray/sum-line']]),
        el('div', { className: 'rx-ed-row rx-ed-row-2' }, rich(props, 'resultLabel', 'p', __('Result label')), rich(props, 'resultValue', 'p', __('Result value'))),
        rich(props, 'note', 'p', __('Note under the sum (optional)')));
    },
    save: save
  });

  /* ---- Call to action ---- */
  var CTA_KINDS = [{ label: __('Inline link'), value: 'inline' }, { label: __('Feature card'), value: 'feature' }, { label: __('Start free panel'), value: 'panel' }, { label: __('Demo panel with form'), value: 'form' }];
  reg('healthray/cta', {
    title: __('Call to action'), icon: 'megaphone', description: __('A link in the text, a "how the software handles this" card, or the demo panel.'),
    attributes: { kind: { type: 'string', default: 'inline' }, title: { type: 'string' }, text: { type: 'string' }, linkLabel: { type: 'string' }, linkKey: { type: 'string' }, featureKey: { type: 'string' } },
    edit: function (props) {
      var k = props.attributes.kind || 'inline';
      var controls = [select(props, 'kind', __('Type'), CTA_KINDS)];
      var body;
      if (k === 'inline') {
        controls.push(select(props, 'linkKey', __('Link to page'), opts(cfg.pages, __('Book a demo (default)'))));
        body = el('p', useBlockProps({ className: 'bk-cta-inline' }), rich(props, 'text', 'span', __('Sentence before the link')), rich(props, 'linkLabel', 'a', __('Link text')));
      } else if (k === 'feature') {
        controls.push(select(props, 'featureKey', __('Feature page'), opts(cfg.features, __('All features'))));
        body = el('aside', useBlockProps({ className: 'bk-cta-feat' }), el('div', null, el('p', { className: 'bk-cta-k' }, __('How the software handles this')), rich(props, 'text', 'p', __('Short text'))), el('span', { className: 'btn btn-secondary btn-sm' }, cfg.features[props.attributes.featureKey] || __('Feature button (from the menu)')));
      } else {
        body = el('aside', useBlockProps({ className: 'bk-cta-panel' + (k === 'form' ? ' bk-cta-split' : '') }), el('div', { className: k === 'form' ? 'bk-cta-l' : null }, rich(props, 'title', 'p', __('Panel title')), rich(props, 'text', 'p', __('Short text'))), k === 'form' ? el('div', { className: 'bk-cta-r' }, el('p', { className: 'bk-cta-fh' }, __('Get a free demo call')), el('p', null, __('The booking form shows here on the page.'))) : el('p', { className: 'bk-cta-btns' }, el('span', { className: 'btn btn-primary' }, __('Start free')), el('span', null, __('See the plans'))));
      }
      return el(Fragment, null, panel(__('Call to action'), controls), body);
    },
    save: function () { return null; }
  });

  /* ---- FAQ ---- */
  reg('healthray/faq-item', {
    title: __('Question'), icon: 'editor-help', parent: ['healthray/faq'], supports: { inserter: false },
    attributes: { question: { type: 'string' } },
    edit: function (props) {
      return el('div', useBlockProps({ className: 'rx-ed-step' }), rich(props, 'question', 'p', __('Question')), inner(['core/paragraph', 'core/list'], [['core/paragraph', { placeholder: __('Answer…') }]]));
    },
    save: save
  });
  reg('healthray/faq', {
    title: __('FAQ'), icon: 'editor-help', description: __('Questions and answers at the end of the guide (also sent to search engines as FAQ data).'),
    attributes: { heading: { type: 'string', default: 'Asked about this' } },
    edit: function (props) {
      return el('div', useBlockProps({ className: 'rx-ed-box' }), rich(props, 'heading', 'h2', __('Heading')),
        inner(['healthray/faq-item'], [['healthray/faq-item'], ['healthray/faq-item']]));
    },
    save: save
  });

  /* ---- Sidebar panel: guide settings (plain post meta) ---- */
  if (wp.plugins && wp.editPost && wp.editPost.PluginDocumentSettingPanel && !cfg.legacy) {
    var useEntityProp = wp.coreData.useEntityProp;
    var useSelect = wp.data.useSelect;
    wp.plugins.registerPlugin('rx-guide-settings', {
      render: function () {
        var pt = useSelect(function (s) { return s('core/editor').getCurrentPostType(); }, []);
        var meta = useEntityProp('postType', 'post', 'meta');
        var content = useSelect(function (s) { var ed = s('core/editor'); return ed.getEditedPostContent ? ed.getEditedPostContent() : ''; }, []);
        if (pt !== 'post') { return null; }
        var m = meta[0] || {};
        function set(k, v) { var o = {}; o[k] = v; meta[1](Object.assign({}, m, o)); }
        var plain = (content || '').replace(/<!--\s*\/?wp:[^\s]+\s*(\{[\s\S]*?\})?\s*\/?-->/g, function (all, j) { return ' ' + (j || '').replace(/"[A-Za-z_]+":|[{}",]/g, ' '); }).replace(/<[^>]+>/g, ' ');
        var words = (plain.match(/[A-Za-z0-9ऀ-ॿ][^\s]*/g) || []).length;
        var mins = Math.max(1, Math.ceil(words / (cfg.wpm || 200)));
        return el(wp.editPost.PluginDocumentSettingPanel, { name: 'rx-guide', title: __('Article settings'), className: 'rx-guide-settings' },
          el(c.SelectControl, { label: __('Topic'), value: m.topic || '', options: opts(cfg.topics, __('Choose a topic')), onChange: function (v) { set('topic', v); } }),
          el(c.TextareaControl, { label: __('Standfirst (the line under the headline)'), value: m.dek || '', rows: 4, onChange: function (v) { set('dek', v); } }),
          el(c.TextControl, { label: __('Updated on (optional)'), type: 'date', value: m.updated || '', onChange: function (v) { set('updated', v); }, help: __('Shown as "Matches the app as of …" in place of the publish date.') }),
          el(c.SelectControl, { label: __('Cover screen (blog cards)'), value: m.cover_screen || '', options: opts(cfg.screens, __('None')), onChange: function (v) { set('cover_screen', v); } }),
          el(c.ToggleControl, { label: __('Featured guide'), checked: !!m.featured, onChange: function (v) { set('featured', v); } }),
          el('p', { style: { margin: 0, color: '#64748B' } }, '≈ ' + words + ' ' + __('words') + ', ' + mins + ' ' + __('min read (counted automatically; the page shows the exact figure).')));
      }
    });
  }
})(window.wp);
