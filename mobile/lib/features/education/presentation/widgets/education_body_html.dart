import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';

/// Renders sanitized education HTML (headings, lists, emphasis).
class EducationBodyHtml extends StatelessWidget {
  const EducationBodyHtml({
    super.key,
    required this.html,
    this.placeholder = 'Belum ada isi konten.',
  });

  final String? html;
  final String placeholder;

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

  @override
  Widget build(BuildContext context) {
    final body = html?.trim() ?? '';
    if (body.isEmpty) {
      return Text(
        placeholder,
        style: TextStyle(color: Colors.grey.shade600),
      );
    }

    final baseStyle = TextStyle(
      fontSize: 16,
      height: 1.5,
      color: Colors.grey.shade800,
    );

    return HtmlWidget(
      body,
      textStyle: baseStyle,
      customStylesBuilder: _stylesForElement,
    );
  }

  Map<String, String>? _stylesForElement(dynamic element) {
    final tag = element.localName as String?;
    if (tag == null || !_allowedTags.contains(tag)) {
      return {'display': 'none'};
    }

    final styles = <String, String>{};

    switch (tag) {
      case 'h1':
        styles.addAll({
          'font-size': '26px',
          'font-weight': '700',
          'margin': '20px 0 10px',
          'line-height': '1.3',
        });
        break;
      case 'h2':
        styles.addAll({
          'font-size': '22px',
          'font-weight': '700',
          'margin': '18px 0 8px',
          'line-height': '1.35',
        });
        break;
      case 'h3':
        styles.addAll({
          'font-size': '20px',
          'font-weight': '600',
          'margin': '16px 0 8px',
        });
        break;
      case 'h4':
        styles.addAll({
          'font-size': '18px',
          'font-weight': '600',
          'margin': '14px 0 6px',
        });
        break;
      case 'h5':
        styles.addAll({
          'font-size': '16px',
          'font-weight': '600',
          'margin': '12px 0 4px',
        });
        break;
      case 'h6':
        styles.addAll({
          'font-size': '15px',
          'font-weight': '600',
          'margin': '10px 0 4px',
        });
        break;
      case 'ul':
        styles.addAll({'padding-left': '22px', 'margin': '10px 0'});
        break;
      case 'ol':
        styles.addAll({'padding-left': '22px', 'margin': '10px 0'});
        break;
      case 'li':
        styles.addAll({'margin': '4px 0'});
        break;
      case 'p':
        styles.addAll({'margin': '10px 0', 'line-height': '1.5'});
        break;
      default:
        break;
    }

    final textAlign = _textAlignFromElement(element);
    if (textAlign != null) {
      styles['text-align'] = textAlign;
    }

    return styles.isEmpty ? null : styles;
  }

  String? _textAlignFromElement(dynamic element) {
    final attributes = element.attributes as Map<String, String>?;
    if (attributes == null) {
      return null;
    }

    final style = attributes['style'];
    if (style != null) {
      final match = RegExp(
        r'text-align\s*:\s*(left|right|center|justify)',
        caseSensitive: false,
      ).firstMatch(style);
      if (match != null) {
        return match.group(1)!.toLowerCase();
      }
    }

    final align = attributes['align']?.toLowerCase().trim();
    if (align != null &&
        {'left', 'right', 'center', 'justify'}.contains(align)) {
      return align;
    }

    return null;
  }
}
