import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/articles/presentation/article_detail_screen.dart';
import '../../features/articles/presentation/articles_screen.dart';
import '../../features/auth/presentation/account_screen.dart';
import '../../features/auth/presentation/forgot_password_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/booking/presentation/booking_screen.dart';
import '../../features/chatbot/presentation/chat_screen.dart';
import '../../features/contact/presentation/contact_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/pages/presentation/generic_page_screen.dart';
import '../../features/projects/presentation/project_detail_screen.dart';
import '../../features/projects/presentation/projects_screen.dart';
import '../../features/services/presentation/service_detail_screen.dart';
import '../../features/services/presentation/services_screen.dart';
import '../../features/tickets/presentation/new_ticket_screen.dart';
import '../../features/tickets/presentation/ticket_detail_screen.dart';
import 'app_shell.dart';
import 'routes.dart';

final rootNavigatorKey = GlobalKey<NavigatorState>(debugLabel: 'root');

final routerProvider = Provider<GoRouter>((ref) {
  final router = GoRouter(
    navigatorKey: rootNavigatorKey,
    initialLocation: Routes.home,
    redirect: (context, state) => state.uri.path == '/' ? Routes.home : null,
    routes: [
      StatefulShellRoute.indexedStack(
        builder: (context, state, shell) => AppShell(shell: shell),
        branches: [
          StatefulShellBranch(
            routes: [GoRoute(path: Routes.home, builder: (_, _) => const HomeScreen())],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.services,
                builder: (_, _) => const ServicesScreen(),
                routes: [
                  GoRoute(
                    path: ':slug',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, state) => ServiceDetailScreen(slug: state.pathParameters['slug']!),
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.work,
                builder: (_, _) => const ProjectsScreen(),
                routes: [
                  GoRoute(
                    path: ':slug',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, state) => ProjectDetailScreen(slug: state.pathParameters['slug']!),
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.articles,
                builder: (_, _) => const ArticlesScreen(),
                routes: [
                  GoRoute(
                    path: ':slug',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, state) => ArticleDetailScreen(slug: state.pathParameters['slug']!),
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.account,
                builder: (_, _) => const AccountScreen(),
                routes: [
                  GoRoute(
                    path: 'register',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, _) => const RegisterScreen(),
                  ),
                  GoRoute(
                    path: 'forgot-password',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, _) => const ForgotPasswordScreen(),
                  ),
                  GoRoute(
                    path: 'tickets/new',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, _) => const NewTicketScreen(),
                  ),
                  GoRoute(
                    path: 'tickets/:id',
                    parentNavigatorKey: rootNavigatorKey,
                    builder: (_, state) => TicketDetailScreen(id: int.tryParse(state.pathParameters['id']!) ?? 0),
                  ),
                ],
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [GoRoute(path: Routes.assistant, builder: (_, _) => const ChatScreen())],
          ),
        ],
      ),
      GoRoute(
        path: Routes.approach,
        builder: (_, _) => const GenericPageScreen(pageKey: 'approach', fallbackTitle: 'My approach'),
      ),
      GoRoute(
        path: Routes.booking,
        builder: (_, state) =>
            BookingScreen(initialServiceId: int.tryParse(state.uri.queryParameters['service'] ?? '')),
      ),
      GoRoute(path: Routes.contact, builder: (_, _) => const ContactScreen()),
    ],
  );
  ref.onDispose(router.dispose);
  return router;
});
