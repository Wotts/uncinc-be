Misschien was de EntityTypeManagerInterface bedoeld voor het ophalen van de objecten, maar deze module wilde graag een $entity_type_id.
Idealiter weet je front-end niets van id's dus gaat het querien op basis van strings.
Vandaar de NodesRepository.

Zoals het nu geschreven is, doe je voor iedere nieuwe pagina de hele query opnieuw. Dat kan ongetwijfeld wel efficienter.

De pagina logica gaat er wel vanuit dat de FE ook begint te tellen bij 0. Hier zou je afspraken over moeten maken, maar het liefst ga je geen +1 doen in je back-end.

Bij het instantieren van $result zou ik direct $result = $nodes; kunnen doen, dat scheelt je een else maar voelt minder netjes.
