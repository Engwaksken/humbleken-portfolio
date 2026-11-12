import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/core/widgets/brand.dart';

void main() {
  testWidgets('falls back to the bundled logo when the site has none', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: BrandLogo(url: null, size: 40, semanticLabel: 'HumbleKen')));
    final image = tester.widget<Image>(find.byType(Image));
    expect((image.image as AssetImage).assetName, BrandLogo.bundledAsset);
    expect(find.bySemanticsLabel('HumbleKen'), findsOneWidget);
  });
}
