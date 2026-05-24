import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:peka_stunting/app.dart';
import 'package:peka_stunting/features/auth/models/user_model.dart';
import 'package:peka_stunting/features/auth/providers/auth_provider.dart';
import 'package:peka_stunting/features/splash/providers/splash_provider.dart';

class _ImmediateGuestAuth extends AuthNotifier {
  @override
  Future<UserModel?> build() async => null;
}

void main() {
  testWidgets('App shows splash then login', (WidgetTester tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          authStateProvider.overrideWith(_ImmediateGuestAuth.new),
          splashImageUrlProvider.overrideWith((ref) async => null),
        ],
        child: const PekaStuntingApp(),
      ),
    );
    await tester.pump();

    expect(find.textContaining('Paket Edukasi Komprehensif'), findsOneWidget);

    await tester.pump(const Duration(milliseconds: 2500));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('Masuk'), findsOneWidget);
  });
}
