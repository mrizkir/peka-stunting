import 'package:html/dom.dart' as dom;
import 'package:html/parser.dart' as html_parser;

/// Normalisasi HTML edukasi dari CMS agar siap ditampilkan di [HtmlWidget].
class EducationHtml {
  EducationHtml._();

  static final _htmlTagPattern = RegExp(r'<\s*\/?\s*[a-z][^>]*>', caseSensitive: false);

  static const _allowedTags = {
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'p',
    'ul',
    'ol',
    'li',
    'br',
    'strong',
    'b',
    'em',
    'i',
  };

  static const _removeTags = {
    'script',
    'style',
    'iframe',
    'object',
    'embed',
    'link',
    'meta',
    'img',
    'a',
    'video',
    'audio',
    'form',
    'input',
    'button',
    'svg',
    'picture',
    'source',
  };

  static const _textAlignValues = {'left', 'right', 'center', 'justify'};

  static bool looksLikeHtml(String value) {
    return _htmlTagPattern.hasMatch(value.trim());
  }

  /// Dekode entitas HTML umum (mis. dari penyimpanan ter-escape).
  static String decodeEntities(String value) {
    return value
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;', "'")
        .replaceAll('&apos;', "'");
  }

  /// Teks polos → paragraf HTML sederhana (seperti editor CMS).
  static String plainTextToHtml(String text) {
    final trimmed = text.trim();
    if (trimmed.isEmpty) {
      return '';
    }

    final blocks = trimmed.split(RegExp(r'\n{2,}'));
    final parts = <String>[];

    for (final block in blocks) {
      final lines = block
          .split('\n')
          .map((line) => line.trim())
          .where((line) => line.isNotEmpty)
          .toList();
      if (lines.isEmpty) {
        continue;
      }

      final escaped = lines.map(_escape).join('<br>');
      parts.add('<p>$escaped</p>');
    }

    return parts.join();
  }

  /// Bersihkan HTML agar hanya tag yang didukung aplikasi (selaras dengan CMS).
  static String sanitize(String html) {
    final trimmed = html.trim();
    if (trimmed.isEmpty) {
      return '';
    }

    final document = html_parser.parse('<div id="__edu_root__">$trimmed</div>');
    final root = document.getElementById('__edu_root__');
    if (root == null) {
      return '';
    }

    _cleanNode(root);

    return root.innerHtml.trim();
  }

  static String prepareForDisplay(String raw) {
    var value = raw.trim();
    if (value.isEmpty) {
      return '';
    }

    value = decodeEntities(value);

    if (!looksLikeHtml(value)) {
      return plainTextToHtml(value);
    }

    return sanitize(value);
  }

  static void _cleanNode(dom.Node node) {
    final children = List<dom.Node>.from(node.nodes);
    for (final child in children) {
      if (child is! dom.Element) {
        continue;
      }

      final tag = child.localName?.toLowerCase();
      if (tag == null) {
        continue;
      }

      if (_removeTags.contains(tag)) {
        child.remove();
        continue;
      }

      if (tag == 'span') {
        if (!_replaceSpanWithSemantic(child)) {
          _unwrapElement(child);
        }
        continue;
      }

      if (tag == 'div') {
        _convertDiv(child);
        continue;
      }

      if (!_allowedTags.contains(tag)) {
        _unwrapElement(child);
        continue;
      }

      _filterAttributes(child);
      _cleanNode(child);
    }
  }

  static bool _replaceSpanWithSemantic(dom.Element span) {
    final style = span.attributes['style'] ?? '';
    final bold = RegExp(r'font-weight\s*:\s*(bold|[6-9]00)', caseSensitive: false)
        .hasMatch(style);
    final italic =
        RegExp(r'font-style\s*:\s*italic', caseSensitive: false).hasMatch(style);

    if (!bold && !italic) {
      return false;
    }

    dom.Element replacement;
    if (bold && italic) {
      replacement = dom.Element.tag('strong');
      final em = dom.Element.tag('em');
      while (span.nodes.isNotEmpty) {
        em.append(span.nodes.first);
      }
      replacement.append(em);
    } else if (bold) {
      replacement = dom.Element.tag('strong');
      while (span.nodes.isNotEmpty) {
        replacement.append(span.nodes.first);
      }
    } else {
      replacement = dom.Element.tag('em');
      while (span.nodes.isNotEmpty) {
        replacement.append(span.nodes.first);
      }
    }

    final parent = span.parent;
    if (parent == null) {
      return false;
    }

    parent.insertBefore(replacement, span);
    span.remove();
    return true;
  }

  static void _convertDiv(dom.Element div) {
    final parent = div.parent;
    final parentTag = parent is dom.Element ? parent.localName?.toLowerCase() : null;

    if (parentTag != null &&
        {'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'td', 'th'}
            .contains(parentTag)) {
      final br = dom.Element.tag('br');
      while (div.nodes.isNotEmpty) {
        div.parent?.insertBefore(div.nodes.first, div);
      }
      div.parent?.insertBefore(br, div);
      div.remove();
      return;
    }

    final paragraph = dom.Element.tag('p');
    while (div.nodes.isNotEmpty) {
      paragraph.append(div.nodes.first);
    }

    if (paragraph.nodes.isEmpty) {
      paragraph.append(dom.Element.tag('br'));
    }

    if (parent == null) {
      return;
    }

    parent.insertBefore(paragraph, div);
    div.remove();
  }

  static void _unwrapElement(dom.Element element) {
    final parent = element.parent;
    if (parent == null) {
      return;
    }

    while (element.nodes.isNotEmpty) {
      parent.insertBefore(element.nodes.first, element);
    }

    element.remove();
  }

  static void _filterAttributes(dom.Element element) {
    String? textAlign;

    final style = element.attributes['style'];
    if (style != null) {
      final match = RegExp(
        r'text-align\s*:\s*(left|right|center|justify)',
        caseSensitive: false,
      ).firstMatch(style);
      textAlign = match?.group(1)?.toLowerCase();
    }

    final align = element.attributes['align']?.toLowerCase().trim();
    if (align != null && _textAlignValues.contains(align)) {
      textAlign ??= align;
    }

    element.attributes.clear();

    if (textAlign != null) {
      element.attributes['style'] = 'text-align: $textAlign';
    }
  }

  static String _escape(String text) {
    return text
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
  }
}
