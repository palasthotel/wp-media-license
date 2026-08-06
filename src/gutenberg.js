import ListOfLicenses from "./components/ListOfLicenses";
import "./appendCaptionToggle";

window.BlockXComponents = {
    ...(window.BlockXComponents || {}),
    ["media-license/list-of-licenses"]: (props)=> <ListOfLicenses
        {...props}
    />,
};