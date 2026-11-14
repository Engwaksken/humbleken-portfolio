import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/features/contact/presentation/payment_details.dart';
import 'package:humbleken_portfolio/features/site/data/site_models.dart';
import 'package:humbleken_portfolio/features/site/data/site_repository.dart';

Future<void> _pump(WidgetTester tester, SiteConfig site) async {
  await tester.pumpWidget(
    ProviderScope(
      overrides: [siteConfigProvider.overrideWithValue(site)],
      child: const MaterialApp(
        home: Scaffold(body: SingleChildScrollView(child: PaymentDetails())),
      ),
    ),
  );
  await tester.pumpAndSettle();
}

void main() {
  testWidgets('shows bank and mobile money details', (tester) async {
    await _pump(
      tester,
      const SiteConfig(
        brandName: 'HumbleKen',
        payments: PaymentOptions(
          bank: BankDetails(bankName: 'Stanbic Bank Uganda', accountNumber: '9030012345678'),
          mobileMoney: MobileMoneyDetails(provider: 'MTN Mobile Money', number: '0770000000'),
          instructions: 'Use your booking reference.',
        ),
      ),
    );

    expect(find.text('Payment options'), findsOneWidget);
    expect(find.text('Stanbic Bank Uganda'), findsOneWidget);
    expect(find.text('9030012345678'), findsOneWidget);
    expect(find.text('MTN Mobile Money'), findsOneWidget);
    expect(find.text('Use your booking reference.'), findsOneWidget);
    expect(find.textContaining('UGX'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('is hidden when no payment option is on', (tester) async {
    await _pump(tester, const SiteConfig(brandName: 'HumbleKen'));

    expect(find.text('Payment options'), findsNothing);
  });
}
