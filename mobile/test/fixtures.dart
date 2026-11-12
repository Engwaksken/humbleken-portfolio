/// JSON fixtures shaped like docs/API.md responses.
library;

Map<String, dynamic> homePageJson({bool projectsEnabled = true, String aside = 'card'}) => {
  'hero': {
    'eyebrow': 'IT consulting',
    'title': 'Turning complex IT challenges into simple solutions',
    'intro': 'Practical engineering help for small teams.',
    'primary_label': 'Explore services',
    'secondary_label': 'Book a consultation',
    'aside': aside,
    'image_url': aside == 'image' ? 'https://example.test/hero.jpg' : null,
    'image_alt': aside == 'image' ? 'Engineer at a server rack' : '',
    'card': {
      'eyebrow': 'How it works',
      'text': 'Three simple steps.',
      'steps': ['Tell us the problem', 'Get a plan', ''],
      'link_label': 'Send an enquiry',
    },
  },
  'services': {
    'enabled': true,
    'eyebrow': 'What we do',
    'heading': 'Services',
    'link_label': 'All services',
    'items': [
      {
        'id': 3,
        'slug': 'network-setup',
        'name': 'Network setup',
        'summary': 'Reliable office networks.',
        'category': {'id': 1, 'name': 'Infrastructure', 'slug': 'infrastructure'},
        'image_url': null,
      },
    ],
  },
  'why': {
    'enabled': true,
    'eyebrow': 'Why us',
    'heading': 'Why work with us',
    'items': [
      {'icon': 'fa-bolt', 'heading': 'Fast response', 'text': 'Same-day replies.', 'image_url': null},
      {'icon': 'fa-unknown-thing', 'heading': 'Plain language', 'text': null, 'image_url': null},
    ],
  },
  'projects': {
    'enabled': projectsEnabled,
    'eyebrow': 'Portfolio',
    'heading': 'Recent work',
    'link_label': 'All projects',
    'items': [
      {
        'id': 9,
        'slug': 'clinic-migration',
        'title': 'Clinic cloud migration',
        'summary': 'Moved a clinic to the cloud.',
        'technologies': ['Azure', 'Laravel'],
        'image_url': null,
        'published_at': '2026-10-01T09:00:00+00:00',
      },
    ],
  },
  'cta': {'enabled': true, 'title': 'Ready to fix it?', 'text': 'Talk to us today.'},
};

Map<String, dynamic> ticketDetailJson() => {
  'id': 42,
  'subject': 'Printer offline',
  'status': 'answered',
  'status_label': 'Answered',
  'is_closed': false,
  'created_at': '2026-11-01T08:30:00+00:00',
  'last_activity_at': '2026-11-02T10:00:00+00:00',
  'replies': [
    {
      'id': 1,
      'body': 'The office printer is offline.',
      'from_staff': false,
      'author_name': 'Jane Customer',
      'created_at': '2026-11-01T08:30:00+00:00',
      'attachments': [
        {
          'index': 0,
          'name': 'error.png',
          'download_url': 'https://example.test/api/v1/tickets/42/replies/1/attachments/0',
        },
      ],
    },
    {
      'id': 2,
      'body': 'Please restart the print spooler.',
      'from_staff': true,
      'author_name': 'Ken',
      'created_at': '2026-11-02T10:00:00+00:00',
      'attachments': [],
    },
  ],
};
