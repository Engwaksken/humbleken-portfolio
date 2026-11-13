import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/utils/format.dart';
import '../../../core/utils/validators.dart';
import '../../../core/widgets/form_widgets.dart';
import '../../../core/widgets/layout.dart';
import '../../pages/data/pages_repository.dart';
import '../../pages/presentation/page_content_view.dart';
import '../../services/presentation/service_dropdown.dart';
import '../data/booking_repository.dart';

class BookingScreen extends ConsumerWidget {
  const BookingScreen({super.key, this.initialServiceId});

  final int? initialServiceId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final page = ref.watch(pageContentProvider('booking')).value;
    return Scaffold(
      appBar: brandedAppBar(context, title: 'Book a consultation'),
      body: BrandedPageBody(
        maxWidth: 720,
        header: page != null ? PageHeader(page: page) : const GradientHeader(title: 'Book a consultation'),
        children: [
          if (page != null) PageContentView(page: page, showSteps: false),
          const SectionHeading(title: 'Request a time', subtitle: 'Choose a time that suits you and I will confirm.'),
          const SizedBox(height: 16),
          SurfaceCard(child: BookingForm(initialServiceId: initialServiceId)),
          if (page != null && page.steps.isNotEmpty) ...[
            const SizedBox(height: 32),
            const SectionHeading(title: 'What happens next'),
            const SizedBox(height: 16),
            PageStepsList(steps: page.steps),
          ],
        ],
      ),
    );
  }
}

class BookingForm extends ConsumerStatefulWidget {
  const BookingForm({super.key, this.initialServiceId});

  final int? initialServiceId;

  @override
  ConsumerState<BookingForm> createState() => _BookingFormState();
}

class _BookingFormState extends ConsumerState<BookingForm> {
  final _formKey = GlobalKey<FormState>();
  final _dateFieldKey = GlobalKey<FormFieldState<DateTime>>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _message = TextEditingController();
  final _errors = ServerErrors();
  late int? _serviceId = widget.initialServiceId;
  DateTime? _preferredAt;
  bool _busy = false;
  String? _success;

  @override
  void dispose() {
    for (final c in [_name, _email, _phone, _message]) {
      c.dispose();
    }
    _errors.dispose();
    super.dispose();
  }

  Future<void> _pickDateTime() async {
    final now = DateTime.now();
    final initial = _preferredAt ?? now.add(const Duration(days: 1));
    final date = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(now.year, now.month, now.day),
      lastDate: now.add(const Duration(days: 365)),
      helpText: 'Preferred date',
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_preferredAt ?? DateTime(now.year, now.month, now.day, 10)),
      helpText: 'Preferred time',
    );
    if (time == null || !mounted) return;
    final picked = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    _errors.clearField('preferred_at');
    setState(() => _preferredAt = picked);
    _dateFieldKey.currentState?.didChange(picked);
  }

  String? _validateDate(DateTime? value) {
    if (value == null) return 'Choose a preferred date and time.';
    if (!value.isAfter(DateTime.now())) return 'Choose a date and time in the future.';
    return null;
  }

  Future<void> _submit() async {
    _errors.clear();
    setState(() => _success = null);
    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _busy = true);
    try {
      final message = await ref
          .read(bookingRepositoryProvider)
          .submit(
            name: _name.text,
            email: _email.text,
            phone: _phone.text,
            serviceId: _serviceId,
            preferredAt: _preferredAt!,
            message: _message.text,
          );
      if (!mounted) return;
      _formKey.currentState?.reset();
      for (final c in [_name, _email, _phone, _message]) {
        c.clear();
      }
      setState(() {
        _preferredAt = null;
        _success = message;
      });
    } on ApiException catch (e) {
      _errors.apply(e);
    } catch (e) {
      _errors.setMessage(ApiException.messageOf(e));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Form(
      key: _formKey,
      child: AutofillGroup(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (_success != null) ...[
              FormStatusMessage(message: _success!, isError: false),
              const SizedBox(height: 16),
            ],
            ListenableBuilder(
              listenable: _errors,
              builder: (context, _) => _errors.message() == null
                  ? const SizedBox.shrink()
                  : Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: FormStatusMessage(message: _errors.message()!),
                    ),
            ),
            ApiTextField(
              controller: _name,
              field: 'name',
              label: 'Name',
              isRequired: true,
              errors: _errors,
              validator: nameRules,
              autofillHints: const [AutofillHints.name],
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _email,
              field: 'email',
              label: 'Email',
              isRequired: true,
              errors: _errors,
              validator: emailValidator,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _phone,
              field: 'phone',
              label: 'Phone (optional)',
              errors: _errors,
              validator: phoneRules,
              keyboardType: TextInputType.phone,
              autofillHints: const [AutofillHints.telephoneNumber],
            ),
            const SizedBox(height: 16),
            ListenableBuilder(
              listenable: _errors,
              builder: (context, _) => ServiceDropdown(
                value: _serviceId,
                errorText: _errors.of('service_id'),
                onChanged: (id) {
                  _errors.clearField('service_id');
                  setState(() => _serviceId = id);
                },
              ),
            ),
            const SizedBox(height: 16),
            ListenableBuilder(
              listenable: _errors,
              builder: (context, _) => FormField<DateTime>(
                key: _dateFieldKey,
                initialValue: _preferredAt,
                validator: _validateDate,
                forceErrorText: _errors.of('preferred_at'),
                builder: (field) => Semantics(
                  button: true,
                  label:
                      'Preferred date and time, required. '
                      '${_preferredAt == null ? 'Not chosen' : formatDateTime(_preferredAt)}',
                  excludeSemantics: true,
                  onTap: _pickDateTime,
                  child: InkWell(
                    onTap: _pickDateTime,
                    borderRadius: BorderRadius.circular(4),
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Preferred date and time (required)',
                        errorText: field.errorText,
                        suffixIcon: const Icon(Icons.calendar_month_outlined),
                      ),
                      isEmpty: _preferredAt == null,
                      child: Text(
                        _preferredAt == null ? '' : formatDateTime(_preferredAt),
                        style: theme.textTheme.bodyLarge,
                      ),
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            ApiTextField(
              controller: _message,
              field: 'message',
              label: 'Anything I should know? (optional)',
              errors: _errors,
              validator: maxLength('message', 5000),
              maxLines: 5,
              minLines: 3,
              keyboardType: TextInputType.multiline,
              textInputAction: TextInputAction.newline,
            ),
            const SizedBox(height: 20),
            SubmitButton(label: 'Request booking', busy: _busy, onPressed: _submit),
          ],
        ),
      ),
    );
  }
}
