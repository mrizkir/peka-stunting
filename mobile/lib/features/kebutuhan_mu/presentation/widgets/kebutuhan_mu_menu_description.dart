import 'package:flutter/material.dart';
import 'package:html/dom.dart' as dom;
import 'package:html/parser.dart' as html_parser;

/// Menampilkan deskripsi menu dengan whitelist elemen selaras backend.
class KebutuhanMuMenuDescription extends StatelessWidget {
  const KebutuhanMuMenuDescription({
    super.key,
    required this.description,
  });

  final String description;

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
    final baseStyle = TextStyle(
      color: Colors.grey.shade700,
      height: 1.5,
      fontSize: 16,
    );

    final blocks = _buildBlocks(description, baseStyle);
    if (blocks.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < blocks.length; i++) ...[
          if (i > 0) const SizedBox(height: 12),
          Text.rich(
            TextSpan(children: blocks[i].spans),
            textAlign: blocks[i].textAlign,
            style: baseStyle,
          ),
        ],
      ],
    );
  }

  List<_HtmlBlock> _buildBlocks(String raw, TextStyle base) {
    final fragment = html_parser.parseFragment(raw);
    final blocks = <_HtmlBlock>[];
    final pendingInline = <InlineSpan>[];

    void flushPendingInline() {
      if (pendingInline.isEmpty) {
        return;
      }
      blocks.add(
        _HtmlBlock(
          spans: List<InlineSpan>.from(pendingInline),
          textAlign: TextAlign.start,
        ),
      );
      pendingInline.clear();
    }

    for (final node in fragment.nodes) {
      if (node is dom.Text) {
        final text = node.text;
        if (text.trim().isNotEmpty) {
          pendingInline.add(TextSpan(text: text, style: base));
        }
        continue;
      }

      if (node is! dom.Element) {
        continue;
      }

      final tag = node.localName?.toLowerCase();
      if (tag == null) {
        continue;
      }

      if (!_allowedTags.contains(tag)) {
        pendingInline.addAll(_collectInline(node, base));
        continue;
      }

      if (tag == 'p' || tag.startsWith('h')) {
        flushPendingInline();
        final headingStyle = tag.startsWith('h') ? _headingStyle(tag, base) : base;
        final spans = _collectInlineChildren(node, headingStyle);
        if (spans.isNotEmpty) {
          blocks.add(
            _HtmlBlock(
              spans: spans,
              textAlign: _parseTextAlign(node),
            ),
          );
        }
        continue;
      }

      if (tag == 'ul' || tag == 'ol') {
        flushPendingInline();
        var order = 0;
        for (final child in node.children) {
          if (child.localName?.toLowerCase() != 'li') {
            continue;
          }

          order += 1;
          final marker = tag == 'ol' ? '$order. ' : '• ';
          final spans = <InlineSpan>[
            TextSpan(text: marker, style: base),
            ..._collectInlineChildren(child, base),
          ];
          if (spans.length > 1 || (spans.first as TextSpan).text!.trim().isNotEmpty) {
            blocks.add(
              _HtmlBlock(
                spans: spans,
                textAlign: _parseTextAlign(child),
              ),
            );
          }
        }
        continue;
      }

      if (tag == 'li') {
        flushPendingInline();
        final spans = <InlineSpan>[
          TextSpan(text: '• ', style: base),
          ..._collectInlineChildren(node, base),
        ];
        blocks.add(
          _HtmlBlock(
            spans: spans,
            textAlign: _parseTextAlign(node),
          ),
        );
        continue;
      }

      pendingInline.addAll(_collectInline(node, base));
    }

    flushPendingInline();

    return blocks
        .where((block) => block.spans.any((span) => (span as TextSpan).text?.trim().isNotEmpty ?? false))
        .toList();
  }

  List<InlineSpan> _collectInlineChildren(dom.Element element, TextStyle style) {
    final spans = <InlineSpan>[];
    for (final child in element.nodes) {
      spans.addAll(_collectInline(child, style));
    }
    return spans;
  }

  List<InlineSpan> _collectInline(dom.Node node, TextStyle style) {
    if (node is dom.Text) {
      if (node.text.isEmpty) {
        return const [];
      }
      return [TextSpan(text: node.text, style: style)];
    }

    if (node is! dom.Element) {
      return const [];
    }

    final tag = node.localName?.toLowerCase();
    if (tag == null) {
      return const [];
    }

    if (!_allowedTags.contains(tag)) {
      final spans = <InlineSpan>[];
      for (final child in node.nodes) {
        spans.addAll(_collectInline(child, style));
      }
      return spans;
    }

    if (tag == 'br') {
      return [TextSpan(text: '\n', style: style)];
    }

    var nextStyle = style;
    if (tag == 'strong' || tag == 'b') {
      nextStyle = style.copyWith(fontWeight: FontWeight.w700);
    } else if (tag == 'em' || tag == 'i') {
      nextStyle = style.copyWith(fontStyle: FontStyle.italic);
    }

    final spans = <InlineSpan>[];
    for (final child in node.nodes) {
      spans.addAll(_collectInline(child, nextStyle));
    }
    return spans;
  }

  TextStyle _headingStyle(String tag, TextStyle base) {
    switch (tag) {
      case 'h1':
        return base.copyWith(fontSize: 26, fontWeight: FontWeight.w700);
      case 'h2':
        return base.copyWith(fontSize: 22, fontWeight: FontWeight.w700);
      case 'h3':
        return base.copyWith(fontSize: 20, fontWeight: FontWeight.w600);
      case 'h4':
        return base.copyWith(fontSize: 18, fontWeight: FontWeight.w600);
      case 'h5':
        return base.copyWith(fontSize: 16, fontWeight: FontWeight.w600);
      case 'h6':
        return base.copyWith(fontSize: 15, fontWeight: FontWeight.w600);
      default:
        return base;
    }
  }

  TextAlign _parseTextAlign(dom.Element element) {
    final styleAttr = element.attributes['style'] ?? '';
    final match = RegExp(
      r'text-align\s*:\s*(left|right|center|justify)',
      caseSensitive: false,
    ).firstMatch(styleAttr);

    final align = (match?.group(1) ?? element.attributes['align'] ?? '').toLowerCase().trim();
    switch (align) {
      case 'center':
        return TextAlign.center;
      case 'right':
        return TextAlign.right;
      case 'justify':
        return TextAlign.justify;
      case 'left':
      default:
        return TextAlign.start;
    }
  }
}

class _HtmlBlock {
  const _HtmlBlock({
    required this.spans,
    required this.textAlign,
  });

  final List<InlineSpan> spans;
  final TextAlign textAlign;
}
