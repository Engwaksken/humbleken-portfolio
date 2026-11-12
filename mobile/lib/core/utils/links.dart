import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

/// Opens [uri] outside the app (browser, mail client, dialer, WhatsApp).
/// Shows a snackbar when nothing can handle it.
Future<void> openExternal(BuildContext context, Uri uri) async {
  final messenger = ScaffoldMessenger.maybeOf(context);
  var opened = false;
  try {
    opened = await launchUrl(uri, mode: LaunchMode.externalApplication);
  } catch (_) {
    opened = false;
  }
  if (!opened) {
    messenger?.showSnackBar(SnackBar(content: Text('Could not open ${uri.toString()}')));
  }
}

Future<void> openExternalUrl(BuildContext context, String? url) async {
  final uri = url == null ? null : Uri.tryParse(url.trim());
  if (uri == null || !uri.hasScheme) {
    ScaffoldMessenger.maybeOf(context)?.showSnackBar(const SnackBar(content: Text('That link is not valid.')));
    return;
  }
  await openExternal(context, uri);
}

Uri mailtoUri(String email) => Uri(scheme: 'mailto', path: email.trim());

Uri telUri(String phone) => Uri(scheme: 'tel', path: phone.replaceAll(RegExp(r'[^0-9+]'), ''));

/// `https://wa.me/<digits>`: WhatsApp requires the number with digits only.
Uri whatsappUri(String number) => Uri.parse('https://wa.me/${number.replaceAll(RegExp(r'\D'), '')}');

/// Turns an embeddable player URL into a URL that opens in the browser or
/// native app (YouTube/Vimeo), leaving other URLs unchanged.
String watchUrlFromEmbed(String url) {
  final uri = Uri.tryParse(url);
  if (uri == null) return url;
  final host = uri.host.toLowerCase();
  final segments = uri.pathSegments;

  if ((host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) &&
      segments.length >= 2 &&
      segments.first == 'embed') {
    return 'https://www.youtube.com/watch?v=${segments[1]}';
  }
  if (host == 'player.vimeo.com' && segments.length >= 2 && segments.first == 'video') {
    return 'https://vimeo.com/${segments[1]}';
  }
  return url;
}
