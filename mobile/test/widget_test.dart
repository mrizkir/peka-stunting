import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:peka_stunting/app.dart';

void main() {
  testWidgets('App renders login screen', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(child: PekaStuntingApp()),
    );
    await tester.pumpAndSettle();

    expect(find.text('Masuk'), findsOneWidget);
  });
}
