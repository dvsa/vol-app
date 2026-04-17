################################################################
#
#   update messaging content text.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' updating messaging content text...') AS '';

UPDATE messaging_content
SET text =
    CASE
        WHEN RAND() < 0.1 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec nec pharetra mi, eu venenatis mauris.'
        WHEN RAND() < 0.2 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum sit amet ex molestie nisi viverra vehicula eget in magna. Suspendisse eu vestibulum dolor. Integer placerat placerat odio at posuere. Quisque vel urna porta metus vestibulum consectetur at nec libero. Nulla sed accumsan nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean sed diam convallis lectus facilisis pellentesque sit amet et dolor. Aenean vel nibh scelerisque, gravida mi ut, scelerisque elit. Quisque porttitor ex quis pellentesque dapibus. Pellentesque eleifend, sapien vel elementum luctus, nisl orci suscipit massa, in eleifend sapien felis quis nisi. Suspendisse efficitur arcu congue, fringilla turpis eu, pellentesque enim. Donec pellentesque vulputate enim, vitae tempus purus cursus at. Cras eget nibh ut velit ornare rhoncus eget non ligula. Morbi eget massa morbi.'
        WHEN RAND() < 0.3 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vitae massa quis diam imperdiet tincidunt id tincidunt dui. In maximus orci a est laoreet tristique. Maecenas vitae volutpat enim. Praesent in egestas tellus, id congue enim. Morbi sed urna sed mi sodales lacinia. Etiam elementum vulputate metus sit amet sollicitudin. Ut tincidunt enim et leo fringilla iaculis. Integer non blandit nunc. Vivamus nulla orci, volutpat ac vulputate a, viverra porta nunc. Vestibulum ut lorem ac ligula morbi.'
        WHEN RAND() < 0.4 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur id suscipit leo, ut feugiat lorem. Integer vitae dui nec diam fringilla porta. Nunc vestibulum at tortor dignissim luctus. In eu feugiat nisl, dapibus sollicitudin massa. Donec sollicitudin volutpat rutrum. Integer lobortis placerat.'
        WHEN RAND() < 0.5 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus arcu est, bibendum ac felis sed, tincidunt tempor ante. Aenean consectetur fringilla tempus. Duis nunc metus, rhoncus sit amet blandit.'
        WHEN RAND() < 0.6 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam elementum dignissim leo sed commodo. Vivamus varius arcu massa, quis mollis ante aliquet eu. Duis aliquet commodo sodales. Cras eleifend justo a neque varius volutpat. Morbi eget purus in leo egestas mattis ultricies ut felis. Integer elementum odio nunc, vel elementum tortor luctus vitae. Cras gravida sem et feugiat convallis nullam.'
        WHEN RAND() < 0.7 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis semper justo in sem vestibulum vehicula. Vivamus tempus purus libero, non efficitur metus vestibulum eu. Etiam consectetur sagittis sodales. Sed pulvinar nisl dictum justo vulputate, quis suscipit lorem convallis. Nam tempor felis nisi, sit amet rhoncus turpis rutrum a. Nunc in odio arcu. Fusce laoreet, massa at accumsan feugiat, neque nisi egestas lorem, id vestibulum diam ipsum in velit. Sed diam velit, consequat ac sollicitudin ut, auctor in nisl. Vivamus et mauris ut ante lobortis pretium. Praesent sed ullamcorper elit. Ut cras.'
        WHEN RAND() < 0.8 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec quam consequat, lacinia justo sit amet, tincidunt eros. Phasellus felis mauris, auctor vel justo et, iaculis lacinia nibh. Maecenas congue vehicula pulvinar. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis at vehicula tortor. Suspendisse vulputate lorem nibh, nec blandit justo iaculis vel. Donec sed blandit sapien, eget convallis massa. Sed ullamcorper diam eu facilisis sodales. Curabitur efficitur orci id mattis tristique. Vivamus varius nisl lacus, nec lacinia purus vulputate id. Duis tempus elementum turpis in aliquam. Nunc sem tellus, pharetra non mollis id, aliquet id non.'
        WHEN RAND() < 0.9 THEN 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel gravida augue. Quisque mattis consequat vehicula. Praesent vitae ex iaculis, bibendum quam accumsan, eleifend libero. In quis rutrum ligula, sed aliquet augue. Proin et elit nulla. Suspendisse sagittis metus in tempor rhoncus. Cras ligula ante, vestibulum porta congue vitae, pretium sit amet justo. Nam lacinia, urna sit amet imperdiet pulvinar, nisl quam tincidunt nulla, vel tempor metus tortor ut sem. Nulla scelerisque eu enim et pulvinar. Duis at lobortis augue. Etiam tristique, elit non vehicula pharetra, dolor lorem vestibulum justo, ut varius nibh dolor vitae diam. Nam quis massa scelerisque libero ultrices finibus. Duis eget auctor diam. Nam venenatis lectus ac ex maximus, lobortis convallis velit laoreet. Sed tellus.'
        ELSE 'Lorem ipsum dolor sit amet, consectetur tincidunt.'
    END;

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...updating messaging content text complete.') AS '';

