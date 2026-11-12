import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/core/widgets/form_widgets.dart';

void main() {
  testWidgets('password fields have an eye toggle that shows and hides the text', (tester) async {
    final controller = TextEditingController(text: 'secret');
    final errors = ServerErrors();
    addTearDown(controller.dispose);
    addTearDown(errors.dispose);

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: Column(
            children: [
              ApiTextField(
                controller: controller,
                field: 'password',
                label: 'Password',
                errors: errors,
                obscureText: true,
              ),
              ApiTextField(controller: TextEditingController(), field: 'name', label: 'Name', errors: errors),
            ],
          ),
        ),
      ),
    );

    EditableText editable() => tester.widget<EditableText>(find.byType(EditableText).first);

    expect(editable().obscureText, isTrue);
    expect(find.byTooltip('Show password'), findsOneWidget, reason: 'only the password field gets a toggle');

    await tester.tap(find.byTooltip('Show password'));
    await tester.pump();
    expect(editable().obscureText, isFalse);
    expect(find.byTooltip('Hide password'), findsOneWidget);

    await tester.tap(find.byTooltip('Hide password'));
    await tester.pump();
    expect(editable().obscureText, isTrue);
  });
}
