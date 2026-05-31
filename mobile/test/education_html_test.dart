import 'package:flutter_test/flutter_test.dart';
import 'package:peka_stunting/core/utils/education_html.dart';

void main() {
  test('prepareForDisplay renders escaped html tags', () {
    const escaped = '&lt;p&gt;Halo&lt;/p&gt;';
    expect(
      EducationHtml.prepareForDisplay(escaped),
      '<p>Halo</p>',
    );
  });

  test('prepareForDisplay wraps plain text in paragraphs', () {
    const plain = 'Baris satu\n\nBaris dua';
    expect(
      EducationHtml.prepareForDisplay(plain),
      '<p>Baris satu</p><p>Baris dua</p>',
    );
  });

  test('looksLikeHtml detects paragraph tags', () {
    expect(EducationHtml.looksLikeHtml('<p>Teks</p>'), isTrue);
    expect(EducationHtml.looksLikeHtml('Teks biasa'), isFalse);
  });

  test('sanitize removes unsupported tags and keeps text', () {
    const raw =
        '<p>Tebal <strong>ya</strong></p><img src="x.png" alt="x"><p>Baris dua</p>';

    final result = EducationHtml.sanitize(raw);

    expect(result, contains('<strong>ya</strong>'));
    expect(result, contains('Baris dua'));
    expect(result, isNot(contains('<img')));
  });

  test('sanitize converts div line breaks to paragraphs', () {
    const raw = '<div>Satu</div><div>Dua</div>';

    final result = EducationHtml.sanitize(raw);

    expect(result, '<p>Satu</p><p>Dua</p>');
  });
}
