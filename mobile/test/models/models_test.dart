import 'package:flutter_test/flutter_test.dart';
import 'package:humbleken_portfolio/core/api/paginated.dart';
import 'package:humbleken_portfolio/features/articles/data/article_models.dart';
import 'package:humbleken_portfolio/features/auth/data/auth_repository.dart';
import 'package:humbleken_portfolio/features/home/data/home_models.dart';
import 'package:humbleken_portfolio/features/services/data/service_models.dart';
import 'package:humbleken_portfolio/features/site/data/site_models.dart';
import 'package:humbleken_portfolio/features/tickets/data/ticket_models.dart';

import '../fixtures.dart';

void main() {
  group('HomePage.fromJson', () {
    test('parses every section', () {
      final page = HomePage.fromJson(homePageJson());

      final hero = page.hero!;
      expect(hero.title, 'Turning complex IT challenges into simple solutions');
      expect(hero.aside, HeroAside.card);
      expect(hero.card!.eyebrow, 'How it works');
      expect(hero.card!.steps, ['Tell us the problem', 'Get a plan'], reason: 'blank steps are dropped');
      expect(hero.card!.linkLabel, 'Send an enquiry');

      expect(page.services.isVisible, isTrue);
      expect(page.services.items.single.slug, 'network-setup');
      expect(page.services.items.single.category!.name, 'Infrastructure');

      expect(page.why.items, hasLength(2));
      expect(page.why.items.first.icon, 'fa-bolt');
      expect(page.why.items.last.text, isNull);

      final project = page.projects.items.single;
      expect(project.technologies, ['Azure', 'Laravel']);
      expect(project.publishedAt!.toUtc(), DateTime.utc(2026, 10, 1, 9));

      expect(page.cta.isVisible, isTrue);
      expect(page.cta.title, 'Ready to fix it?');
    });

    test('respects enabled flags and image aside', () {
      final page = HomePage.fromJson(homePageJson(projectsEnabled: false, aside: 'image'));
      expect(page.projects.isVisible, isFalse);
      expect(page.hero!.aside, HeroAside.image);
      expect(page.hero!.imageAlt, 'Engineer at a server rack');
    });

    test('tolerates missing sections and unknown aside', () {
      final page = HomePage.fromJson({
        'hero': {'title': 'Hi', 'aside': 'video'},
      });
      expect(page.hero!.aside, HeroAside.none);
      expect(page.services.isVisible, isFalse);
      expect(page.why.items, isEmpty);
      expect(page.cta.isVisible, isFalse);
    });
  });

  group('TicketDetail.fromJson', () {
    test('parses status, replies and attachments', () {
      final ticket = TicketDetail.fromJson(ticketDetailJson());

      expect(ticket.id, 42);
      expect(ticket.status, TicketStatus.answered);
      expect(ticket.statusLabel, 'Answered');
      expect(ticket.isClosed, isFalse);
      expect(ticket.replies, hasLength(2));

      final first = ticket.replies.first;
      expect(first.fromStaff, isFalse);
      expect(first.attachments.single.name, 'error.png');
      expect(first.attachments.single.index, 0);

      final second = ticket.replies.last;
      expect(second.fromStaff, isTrue);
      expect(second.authorName, 'Ken');
      expect(second.attachments, isEmpty);
    });

    test('a closed status implies is_closed when the flag is missing', () {
      final ticket = TicketSummary.fromJson({'id': 1, 'subject': 'x', 'status': 'closed'});
      expect(ticket.isClosed, isTrue);
      expect(ticket.statusLabel, 'closed');
    });
  });

  group('Paginated.fromJson', () {
    test('reads meta and items', () {
      final page = Paginated.fromJson({
        'data': [
          {'id': 1, 'slug': 'a', 'title': 'A', 'category': null, 'tags': [], 'reading_minutes': 4},
          {
            'id': 2,
            'slug': 'b',
            'title': 'B',
            'category': {'name': 'Networking', 'slug': 'networking'},
            'tags': [
              {'name': 'wifi', 'slug': 'wifi'},
            ],
          },
        ],
        'meta': {'current_page': 1, 'last_page': 3, 'per_page': 10, 'total': 25},
      }, ArticleSummary.fromJson);

      expect(page.items, hasLength(2));
      expect(page.items.first.readingMinutes, 4);
      expect(page.items.last.category!.slug, 'networking');
      expect(page.items.last.tags.single.name, 'wifi');
      expect(page.meta.currentPage, 1);
      expect(page.meta.lastPage, 3);
      expect(page.meta.perPage, 10);
      expect(page.meta.total, 25);
      expect(page.meta.hasMore, isTrue);
    });

    test('last page has no more', () {
      final meta = PageMeta.fromJson({'current_page': 3, 'last_page': 3, 'per_page': 10, 'total': 25});
      expect(meta.hasMore, isFalse);
    });

    test('missing meta is treated as a single page', () {
      final page = Paginated.fromJson({'data': <Object>[]}, ArticleSummary.fromJson);
      expect(page.items, isEmpty);
      expect(page.meta.hasMore, isFalse);
    });
  });

  group('SiteConfig.fromJson', () {
    test('parses theme, contact, features and maintenance', () {
      final site = SiteConfig.fromJson({
        'brand_name': 'IT Problem Solver',
        'logo_url': 'https://example.test/site-assets/logo',
        'favicon_url': 'https://example.test/site-assets/favicon',
        'theme': {'primary': '#077784', 'secondary': '#14263d'},
        'contact': {'email': 'hello@example.test', 'phone': null, 'whatsapp': '+254 700 000 000'},
        'social_links': [
          {'platform': 'linkedin', 'url': 'https://linkedin.com/in/x'},
          {'platform': 'github', 'url': ''},
        ],
        'features': {'projects': true, 'articles': false, 'chatbot': true},
        'maintenance': {'enabled': true, 'message': 'Back soon'},
      });
      expect(site.primaryHex, '#077784');
      expect(site.contact.email, 'hello@example.test');
      expect(site.contact.phone, isNull);
      expect(site.socialLinks.single.label, 'LinkedIn');
      expect(site.features.articles, isFalse);
      expect(site.features.chatbot, isTrue);
      expect(site.maintenanceEnabled, isTrue);
      expect(site.maintenanceMessage, 'Back soon');
    });
  });

  group('ServiceCatalogue.grouped', () {
    test('groups by category order with uncategorised last', () {
      final catalogue = ServiceCatalogue.fromJson({
        'data': [
          {
            'id': 1,
            'slug': 'a',
            'name': 'A',
            'category': {'id': 2, 'name': 'Cloud', 'slug': 'cloud'},
          },
          {'id': 2, 'slug': 'b', 'name': 'B', 'category': null},
          {
            'id': 3,
            'slug': 'c',
            'name': 'C',
            'category': {'id': 1, 'name': 'Networks', 'slug': 'networks'},
          },
        ],
        'categories': [
          {'id': 1, 'name': 'Networks', 'slug': 'networks', 'description': null},
          {'id': 2, 'name': 'Cloud', 'slug': 'cloud', 'description': 'Cloud work'},
        ],
      });
      final groups = catalogue.grouped();
      expect(groups.map((g) => g.title), ['Networks', 'Cloud', 'Other services']);
      expect(groups[1].description, 'Cloud work');
      expect(groups.last.services.single.slug, 'b');
    });
  });

  group('Currency and payments', () {
    test('service detail prefers the formatted price', () {
      final service = ServiceDetail.fromJson({
        'id': 1,
        'slug': 's',
        'name': 'S',
        'indicative_price': '250000.00',
        'indicative_price_formatted': 'UGX 250,000',
      });
      expect(service.indicativePrice, 'UGX 250,000');

      final older = ServiceDetail.fromJson({'id': 1, 'slug': 's', 'name': 'S', 'indicative_price': '250000.00'});
      expect(older.indicativePrice, '250000.00');
    });

    test('site config parses currency and payment options', () {
      final site = SiteConfig.fromJson({
        'brand_name': 'HumbleKen',
        'currency': {'code': 'UGX', 'symbol': 'UGX', 'decimals': 0},
        'payments': {
          'iotec': true,
          'bank': {'bank_name': 'Stanbic Bank Uganda', 'account_number': '9030012345678'},
          'mobile_money': null,
          'instructions': 'Use your booking reference.',
        },
      });
      expect(site.currency.code, 'UGX');
      expect(site.payments.iotec, isTrue);
      expect(site.payments.bank!.bankName, 'Stanbic Bank Uganda');
      expect(site.payments.mobileMoney, isNull);
      expect(site.payments.isEmpty, isFalse);
    });

    test('older servers without payments default to UGX and no options', () {
      final site = SiteConfig.fromJson({'brand_name': 'HumbleKen'});
      expect(site.currency.code, 'UGX');
      expect(site.payments.isEmpty, isTrue);
    });
  });

  group('AppUser.fromJson', () {
    test('reads the ticket reply email preference, defaulting to on', () {
      expect(AppUser.fromJson({'id': 1, 'name': 'A', 'email': 'a@b.c'}).ticketReplyEmails, isTrue);
      expect(
        AppUser.fromJson({
          'id': 1,
          'name': 'A',
          'email': 'a@b.c',
          'notification_preferences': {
            'ticket_reply': {'mail': false},
          },
        }).ticketReplyEmails,
        isFalse,
      );
    });
  });
}
