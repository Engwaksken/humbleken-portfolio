/// Route paths in one place.
abstract final class Routes {
  static const home = '/home';
  static const services = '/services';
  static const work = '/work';
  static const articles = '/articles';
  static const account = '/account';

  static const approach = '/approach';
  static const booking = '/booking';
  static const contact = '/contact';
  static const assistant = '/assistant';

  static const register = '/account/register';
  static const forgotPassword = '/account/forgot-password';
  static const newTicket = '/account/tickets/new';

  static String service(String slug) => '$services/${Uri.encodeComponent(slug)}';
  static String project(String slug) => '$work/${Uri.encodeComponent(slug)}';
  static String article(String slug) => '$articles/${Uri.encodeComponent(slug)}';
  static String ticket(int id) => '$account/tickets/$id';
  static String bookingFor(int? serviceId) => serviceId == null ? booking : '$booking?service=$serviceId';
}
