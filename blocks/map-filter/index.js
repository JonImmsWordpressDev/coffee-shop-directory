console.log('✅ map-filter block loaded');

wp.blocks.registerBlockType('ocd/map-filter', {
    title: 'Map & Filters',
    icon: 'location-alt',
    category: 'widgets',
    edit: () => wp.element.createElement('div', null, '🗺️ Map & Filter block placeholder'),
    save: () => null
});
