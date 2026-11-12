import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/services_repository.dart';

/// Optional "Which service?" dropdown populated from `GET /services`.
/// Hidden if the services cannot be loaded, since the field is optional.
class ServiceDropdown extends ConsumerWidget {
  const ServiceDropdown({super.key, required this.value, required this.onChanged, this.errorText});

  final int? value;
  final ValueChanged<int?> onChanged;
  final String? errorText;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final catalogue = ref.watch(serviceCatalogueProvider);
    if (catalogue.hasError && !catalogue.hasValue) return const SizedBox.shrink();

    final services = catalogue.value?.services ?? const [];
    final selected = services.any((s) => s.id == value) ? value : null;

    return DropdownButtonFormField<int?>(
      // Re-create when the list arrives so a preselected service shows.
      key: ValueKey('service-dropdown-${services.length}-$selected'),
      initialValue: selected,
      isExpanded: true,
      decoration: InputDecoration(
        labelText: 'Service (optional)',
        helperText: catalogue.isLoading && services.isEmpty ? 'Loading services…' : null,
        errorText: errorText,
      ),
      items: [
        const DropdownMenuItem<int?>(value: null, child: Text('Not sure yet / general')),
        for (final s in services)
          DropdownMenuItem<int?>(
            value: s.id,
            child: Text(s.name, overflow: TextOverflow.ellipsis),
          ),
      ],
      onChanged: services.isEmpty ? null : onChanged,
    );
  }
}
