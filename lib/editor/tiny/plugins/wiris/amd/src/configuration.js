import {
    imageButtonNameMathType,
    imageButtonNameChemType
} from './common';
import {
    addMenubarItem,
} from 'editor_tiny/utils';

// Name of the default equation editor in Tiny
const TINY_EQUATION = 'tiny_equation';

const configureMenu = (menu) => {
    //#14351 - Unblocked tiny_equation to the extent that this plugin (tuny_wiris) is displayed.
    addMenubarItem(menu, 'insert', imageButtonNameMathType);
    addMenubarItem(menu, 'insert', imageButtonNameChemType);

    return menu;
};

const configureToolbar = (toolbar) => {
    // In such case, add MathType and ChemType directly, in section `content`
    const allButtons = toolbar.flatMap((section) => section.items);
    const hasMathType = allButtons.includes(imageButtonNameMathType);
    const hasChemType = allButtons.includes(imageButtonNameChemType);
    if (!hasMathType || !hasChemType) {
        toolbar = toolbar.map((section) => {
            if (section.name === 'content') {
                if (!hasMathType) {
                    section.items.unshift(imageButtonNameChemType);
                }
                if (!hasChemType) {
                    section.items.unshift(imageButtonNameMathType);
                }
            }
            return section;
        });
    }

    return toolbar;
};

export const configure = (instanceConfig) => {
    // Update the instance configuration to add the Media menu option to the menus and toolbars and upload_handler.
    return {
        toolbar: configureToolbar(instanceConfig.toolbar),
        menu: configureMenu(instanceConfig.menu),
    };
};
