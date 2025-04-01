const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { TextControl, ToggleControl, SelectControl, Spinner } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { createElement, useState, useEffect } = wp.element;

const CoffeeShopMetaPanel = () => {
    const meta = useSelect((select) =>
        select('core/editor').getEditedPostAttribute('meta') || {}
    );

    const { editPost } = useDispatch('core/editor');

    const neighborhoods = useSelect((select) =>
        select('core').getEntityRecords('taxonomy', 'neighborhood', {
            per_page: -1,
            hide_empty: false,
        }) || []
    );

    const neighborhoodOptions = [
        { label: 'Select a neighborhood', value: '' },
        ...neighborhoods.map((term) => ({
            label: term.name,
            value: term.slug,
        })),
    ];

    const [address, setAddress] = useState(meta._ocd_address || '');
    const [isGeocoding, setIsGeocoding] = useState(false);

    useEffect(() => {
        setAddress(meta._ocd_address || '');
    }, [meta._ocd_address]);

    const handleAddressChange = (value) => {
        setAddress(value);

        clearTimeout(window._geocodeTimer);
        window._geocodeTimer = setTimeout(() => {
            if (value.trim().length > 5) {
                setIsGeocoding(true);

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(value)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const result = data[0];
                            editPost({
                                meta: {
                                    _ocd_address: value,
                                    _ocd_latitude: result.lat,
                                    _ocd_longitude: result.lon,
                                }
                            });
                        } else {
                            editPost({ meta: { _ocd_address: value } });
                        }
                        setIsGeocoding(false);
                    })
                    .catch(() => setIsGeocoding(false));
            } else {
                editPost({ meta: { _ocd_address: value } });
            }
        }, 800);
    };

    const fields = [
        createElement(TextControl, {
            label: 'Address',
            value: address,
            onChange: handleAddressChange,
            help: isGeocoding ? 'Looking up coordinates…' : '',
        }),
        isGeocoding && createElement(Spinner, {}),

        createElement(ToggleControl, {
            label: 'WiFi Available',
            checked: meta._ocd_wifi === '1',
            onChange: (val) => editPost({ meta: { _ocd_wifi: val ? '1' : '0' } }),
        }),
        createElement(ToggleControl, {
            label: 'Drive-Thru',
            checked: meta._ocd_drive_thru === '1',
            onChange: (val) => editPost({ meta: { _ocd_drive_thru: val ? '1' : '0' } }),
        }),
        createElement(TextControl, {
            label: 'Latitude',
            value: meta._ocd_latitude || '',
            onChange: (value) => editPost({ meta: { _ocd_latitude: value } }),
        }),
        createElement(TextControl, {
            label: 'Longitude',
            value: meta._ocd_longitude || '',
            onChange: (value) => editPost({ meta: { _ocd_longitude: value } }),
        }),
        createElement(SelectControl, {
            label: 'Neighborhood',
            value: meta._ocd_neighborhood || '',
            options: neighborhoodOptions,
            onChange: (value) => editPost({ meta: { _ocd_neighborhood: value } }),
        }),
        createElement(TextControl, {
            label: 'Website',
            value: meta._ocd_website || '',
            onChange: (value) => editPost({ meta: { _ocd_website: value } }),
        }),
    ];

    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    days.forEach((day) => {
        const openKey = `_ocd_hours_${day.toLowerCase()}_open`;
        const closeKey = `_ocd_hours_${day.toLowerCase()}_close`;

        fields.push(
            createElement('div', { className: 'time-row' },
                createElement(TextControl, {
                    label: `${day} Open`,
                    type: 'time',
                    value: meta[openKey] || '',
                    onChange: (value) => editPost({ meta: { [openKey]: value } }),
                }),
                createElement(TextControl, {
                    label: `${day} Close`,
                    type: 'time',
                    value: meta[closeKey] || '',
                    onChange: (value) => editPost({ meta: { [closeKey]: value } }),
                })
            )
        );
    });

    return createElement(
        PluginDocumentSettingPanel,
        {
            name: 'coffee-shop-meta-panel',
            title: 'Coffee Shop Details',
            className: 'coffee-shop-meta-panel',
        },
        ...fields
    );
};

registerPlugin('coffee-shop-meta-panel', {
    render: CoffeeShopMetaPanel,
    icon: 'admin-site-alt3',
});
